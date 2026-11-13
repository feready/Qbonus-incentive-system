<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Imports\BonusImport;
use App\Models\BonusSubmission;
use App\Services\BonusValidator;
use App\Services\BonusCalculator;
use App\Services\ReferenceWorkbookService;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class UploadController extends Controller
{
    public function index(ReferenceWorkbookService $reference)
    {
        return view('upload.index', [
            'stores' => $reference->getStores(),
        ]);
    }

    public function upload(
        Request $request,
        BonusValidator $validator,
        BonusCalculator $calculator,
        ReferenceWorkbookService $reference
    ) {
        $stores = $reference->getStores();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,xlsm',

            'store_code' => [
                'required',
                Rule::in(array_column($stores, 'code')),
            ],
        ]);

        $store = collect($stores)->firstWhere(
            'code',
            (string) $request->store_code
        );

        if (!$store) {
            return back()->withErrors([
                'store_code' => 'Toko tidak ditemukan.',
            ])->withInput();
        }

        $file = $request->file('file');

        $filePath = $file->store(
            'bonus-submissions',
            'local'
        );

        if (!$filePath) {
            throw new RuntimeException(
                'File submission gagal disimpan.'
            );
        }

        try {
            // IMPORT EXCEL
            $import = new BonusImport();

            Excel::import($import, $file);

            // VALIDASI
            $validation = $validator->validate(
                $import->rows
            );

            $headers = $import->rows->isNotEmpty()
                ? $import->rows->first()
                    ->map(fn ($value) => trim((string) $value))
                    ->values()
                    ->toArray()
                : [];

            $validatedRows = $validation['rows'] ?? [];

            $summary = [
                'match' => 0,
                'difference' => 0,
                'error' => 0,
            ];

            // KALKULASI SETIAP KARYAWAN
            foreach ($validatedRows as &$validatedRow) {

                $data = $validatedRow['data'] ?? [];

                $mapped = [];

                foreach ($headers as $index => $header) {
                    $mapped[$header] = $data[$index] ?? null;
                }

                // Baris yang gagal validasi tidak dihitung.
                if ($validatedRow['status'] === 'ERROR') {
                    $validatedRow['calculation'] = null;
                    $validatedRow['comparison'] = 'ERROR';

                    $summary['error']++;

                    continue;
                }

                try {
                    $calculation = $calculator->calculate([
                        'Bln' => $mapped['Bln'],
                        'Code' => $mapped['Code'],
                        'ZONA' => $mapped['ZONA'],
                        'CAT' => $mapped['CAT'],
                        'Usia' => $mapped['Usia'],
                    ]);

                    $excelFinal = $mapped['Final Insentif'] ?? null;

                    if (
                        $excelFinal === null ||
                        trim((string) $excelFinal) === '' ||
                        !is_numeric($excelFinal)
                    ) {
                        throw new RuntimeException(
                            'Final Insentif Excel kosong atau tidak valid.'
                        );
                    }

                    $selisih = (float) $calculation['Final Insentif QBonus']
                        - (float) $excelFinal;

                    $comparison = abs($selisih) < 0.01
                        ? 'MATCH'
                        : 'DIFFERENCE';

                    $validatedRow['calculation'] = $calculation;
                    $validatedRow['excel_final'] = (float) $excelFinal;
                    $validatedRow['selisih'] = $selisih;
                    $validatedRow['comparison'] = $comparison;

                    $summary[strtolower($comparison)]++;

                } catch (Throwable $e) {

                    $validatedRow['calculation'] = null;
                    $validatedRow['comparison'] = 'ERROR';
                    $validatedRow['status'] = 'ERROR';

                    $validatedRow['errors'][] = $e->getMessage();

                    $summary['error']++;
                }
            }

            unset($validatedRow);

            $calculationStatus = $summary['error'] > 0
                ? 'ERROR'
                : ($summary['difference'] > 0
                    ? 'DIFFERENCE'
                    : ($summary['match'] > 0 ? 'MATCH' : 'ERROR'));

            // SIMPAN SUBMISSION DAN DETAIL KARYAWAN
            $submission = DB::transaction(function () use (
                $request,
                $file,
                $filePath,
                $store,
                $validation,
                $headers,
                &$validatedRows,
                $summary,
                $calculationStatus
            ) {
                $submission = BonusSubmission::create([
                    'user_id' => auth()->id(),

                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $filePath,

                    'store_code' => $store['code'],
                    'store_name' => $store['name'],

                    'validation_status' => $validation['status'] ?? 'ERROR',
                    'calculation_status' => $calculationStatus,

                    'total_rows' => count($validatedRows),
                    'match_count' => $summary['match'],
                    'difference_count' => $summary['difference'],
                    'error_count' => $summary['error'],

                    'validation_summary' => [
                        'status' => $validation['status'] ?? 'ERROR',
                        'message' => $validation['message'] ?? '',
                        'total' => $validation['total'] ?? 0,
                        'valid' => $validation['valid'] ?? 0,
                        'warning' => $validation['warning'] ?? 0,
                        'error' => $validation['error'] ?? 0,
                        'errors' => $validation['errors'] ?? [],
                        'headers' => $headers,
                    ],

                    'calculation_summary' => $summary,
                    'results' => [],
                    'decision_status' => 'PENDING',
                ]);

                foreach ($validatedRows as &$validatedRow) {

                    $data = $validatedRow['data'] ?? [];
                    $mapped = [];

                    foreach ($headers as $index => $header) {
                        $mapped[$header] = $data[$index] ?? null;
                    }

                    $calculation = $validatedRow['calculation'] ?? null;
                    $comparison = $validatedRow['comparison'] ?? 'ERROR';

                    // Hanya DIFFERENCE yang memerlukan keputusan.
                    $decisionStatus = $comparison === 'DIFFERENCE'
                        ? 'PENDING'
                        : 'NOT_REQUIRED';

                    $item = $submission->items()->create([
                        'excel_row' => $validatedRow['excel_row'],

                        'employee_id' => $mapped['ID'] ?? null,
                        'employee_name' => $mapped['Name'] ?? null,
                        'employee_code' => $mapped['Code'] ?? null,

                        'source_data' => $mapped,
                        'calculation_data' => $calculation,

                        'excel_final' => $validatedRow['excel_final'] ?? null,

                        'qbonus_final' => $calculation
                            ? $calculation['Final Insentif QBonus']
                            : null,

                        'difference' => $validatedRow['selisih'] ?? null,

                        'calculation_status' => $comparison,
                        'decision_status' => $decisionStatus,
                    ]);

                    $validatedRow['item_id'] = $item->id;
                    $validatedRow['decision_status'] = $decisionStatus;
                }

                unset($validatedRow);

                $submission->update([
                    'results' => $validatedRows,
                ]);

                return $submission;
            });

            $validation['rows'] = $validatedRows;

            return view('upload.preview', [
                'rows' => $import->rows,
                'validation' => $validation,
                'calculationSummary' => $summary,
                'submission' => $submission,
            ]);

        } catch (Throwable $e) {

            Storage::disk('local')->delete($filePath);

            throw $e;
        }
    }
}
