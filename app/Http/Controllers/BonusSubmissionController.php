<?php

namespace App\Http\Controllers;

use App\Models\BonusSubmission;
use App\Models\BonusSubmissionItem;
use Illuminate\Http\Request;
use App\Exports\BonusSubmissionSummaryExport;
use Maatwebsite\Excel\Facades\Excel;

class BonusSubmissionController extends Controller
{
    /**
     * Pastikan submission milik pengguna yang login.
     */
    private function checkSubmissionOwner(
        BonusSubmission $submission
    ): void {
        abort_unless(
            (int) $submission->user_id === (int) auth()->id(),
            403,
            'Anda tidak memiliki akses ke submission ini.'
        );
    }

    /**
     * Pastikan item karyawan milik pengguna yang login.
     */
    private function checkItemOwner(
        BonusSubmissionItem $item
    ): void {
        $item->loadMissing('submission');

        abort_unless(
            (int) $item->submission->user_id === (int) auth()->id(),
            403,
            'Anda tidak memiliki akses ke data karyawan ini.'
        );
    }

    /**
     * Menampilkan ringkasan seluruh submission milik pengguna login.
     */
    public function index()
    {
        $userId = auth()->id();

        $baseQuery = BonusSubmission::query()
            ->where('user_id', $userId);

        $summary = [
            'total_submissions' => (clone $baseQuery)->count(),
            'total_employees' => (clone $baseQuery)->sum('total_rows'),
            'match' => (clone $baseQuery)->sum('match_count'),
            'difference' => (clone $baseQuery)->sum('difference_count'),
            'error' => (clone $baseQuery)->sum('error_count'),
        ];

        $submissions = (clone $baseQuery)
            ->latest()
            ->paginate(15);

        $nonMatches = BonusSubmissionItem::with('submission')
            ->whereHas('submission', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereIn('calculation_status', ['DIFFERENCE', 'ERROR'])
            ->latest()
            ->limit(100)
            ->get();

        return view('bonus-submissions.index', [
            'summary' => $summary,
            'submissions' => $submissions,
            'nonMatches' => $nonMatches,
        ]);
    }

    /**
     * Export ringkasan submission ke Excel.
     */
    public function exportExcel()
    {
        $userId = auth()->id();

        $baseQuery = BonusSubmission::query()
            ->where('user_id', $userId);

        $summary = [
            'total_submissions' => (clone $baseQuery)->count(),
            'total_employees' => (clone $baseQuery)->sum('total_rows'),
            'match' => (clone $baseQuery)->sum('match_count'),
            'difference' => (clone $baseQuery)->sum('difference_count'),
            'error' => (clone $baseQuery)->sum('error_count'),
        ];

        $submissions = (clone $baseQuery)
            ->latest()
            ->get();

        $nonMatches = BonusSubmissionItem::with('submission')
            ->whereHas('submission', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereIn('calculation_status', ['DIFFERENCE', 'ERROR'])
            ->latest()
            ->get();

        $filename = 'QBonus_Submission_Summary_'
            . now()->format('Ymd_His')
            . '.xlsx';

        return Excel::download(
            new BonusSubmissionSummaryExport(
                $summary,
                $submissions,
                $nonMatches
            ),
            $filename
        );
    }

    /**
     * Menampilkan detail submission.
     */
    public function show(BonusSubmission $submission)
    {
        $this->checkSubmissionOwner($submission);

        $headers = $submission->validation_summary['headers'] ?? [];

        $storedResults = collect($submission->results ?? [])
            ->keyBy('excel_row');

        $items = $submission->items()
            ->orderBy('excel_row')
            ->get();

        $detailRows = $items->map(function ($item) use (
            $headers,
            $storedResults
        ) {
            $sourceData = $item->source_data ?? [];

            $original = $storedResults->get(
                $item->excel_row,
                []
            );

            $data = [];

            foreach ($headers as $header) {
                $data[] = $sourceData[$header] ?? null;
            }

            return [
                'excel_row' => $item->excel_row,
                'data' => $data,

                'status' => $item->calculation_status === 'ERROR'
                    ? 'ERROR'
                    : ($original['status'] ?? 'VALID'),

                'errors' => $original['errors'] ?? [],
                'warnings' => $original['warnings'] ?? [],

                'calculation' => $item->calculation_data,

                'excel_final' => $item->excel_final,
                'selisih' => $item->difference,

                'comparison' => $item->calculation_status,

                'item_id' => $item->id,

                'decision_status' => $item->decision_status,
                'appeal_reason' => $item->appeal_reason,
                'decided_at' => $item->decided_at,
            ];
        });

        $validation = $submission->validation_summary ?? [];

        $validation['rows'] = $detailRows->values()->all();

        return view('upload.preview', [
            'rows' => collect([collect($headers)]),
            'validation' => $validation,
            'calculationSummary' => $submission->calculation_summary ?? [
                'match' => 0,
                'difference' => 0,
                'error' => 0,
            ],
            'submission' => $submission,
        ]);
    }

    /**
     * Approve satu karyawan.
     */
    public function approve(BonusSubmissionItem $item)
    {
        $this->checkItemOwner($item);

        if (
            $item->calculation_status !== 'DIFFERENCE' ||
            $item->decision_status !== 'PENDING'
        ) {
            return back()->with(
                'error',
                'Baris ini tidak dapat di-approve.'
            );
        }

        $item->update([
            'decision_status' => 'APPROVED',
            'appeal_reason' => null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()
            ->route('bonus-submissions.show', $item->bonus_submission_id)
            ->with(
                'success',
                'Karyawan ' . $item->employee_name . ' berhasil di-approve.'
            );
    }

    /**
     * Appeal satu karyawan.
     */
    public function appeal(
        Request $request,
        BonusSubmissionItem $item
    ) {
        $this->checkItemOwner($item);

        if (
            $item->calculation_status !== 'DIFFERENCE' ||
            $item->decision_status !== 'PENDING'
        ) {
            return back()->with(
                'error',
                'Baris ini tidak dapat diajukan appeal.'
            );
        }

        $validated = $request->validate([
            'appeal_reason' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ], [
            'appeal_reason.required' => 'Alasan appeal wajib diisi.',
            'appeal_reason.min' => 'Alasan minimal 5 karakter.',
            'appeal_reason.max' => 'Alasan maksimal 5000 karakter.',
        ]);

        $item->update([
            'decision_status' => 'APPEALED',
            'appeal_reason' => $validated['appeal_reason'],
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()
            ->route('bonus-submissions.show', $item->bonus_submission_id)
            ->with(
                'success',
                'Appeal karyawan ' . $item->employee_name . ' berhasil diajukan.'
            );
    }
}
