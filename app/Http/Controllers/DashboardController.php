<?php

namespace App\Http\Controllers;

use App\Models\BonusSubmission;
use App\Models\BonusSubmissionItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        // Periode bulan berjalan.
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Submission milik pengguna pada bulan ini.
        $monthQuery = BonusSubmission::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [
                $startOfMonth,
                $endOfMonth,
            ]);

        $summary = [
            'total_submissions' => (clone $monthQuery)->count(),

            'total_employees' => (clone $monthQuery)->sum('total_rows'),

            'match' => (clone $monthQuery)->sum('match_count'),

            'difference' => (clone $monthQuery)->sum('difference_count'),

            'error' => (clone $monthQuery)->sum('error_count'),

            // Seluruh submission, bukan hanya bulan ini.
            'pending_decisions' => BonusSubmissionItem::query()
                ->whereHas('submission', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->where('calculation_status', 'DIFFERENCE')
                ->where('decision_status', 'PENDING')
                ->count(),
        ];

        // Lima submission terakhir milik pengguna.
        $recentSubmissions = BonusSubmission::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'summary' => $summary,
            'recentSubmissions' => $recentSubmissions,
            'currentMonth' => now()->format('m/Y'),
        ]);
    }
}
