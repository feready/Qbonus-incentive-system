<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusSubmission;
use App\Models\BonusSubmissionItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $summary = [
            'total_users' => User::where(
                'role',
                User::ROLE_USER
            )->count(),

            'total_super_admins' => User::where(
                'role',
                User::ROLE_SUPER_ADMIN
            )->count(),

            'total_submissions' => BonusSubmission::count(),

            'total_employees' => BonusSubmission::sum('total_rows'),

            'match' => BonusSubmission::sum('match_count'),

            'difference' => BonusSubmission::sum('difference_count'),

            'error' => BonusSubmission::sum('error_count'),

            'pending_decisions' => BonusSubmissionItem::where(
                'calculation_status',
                'DIFFERENCE'
            )->where(
                'decision_status',
                'PENDING'
            )->count(),
        ];

        $recentSubmissions = BonusSubmission::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'summary' => $summary,
            'recentSubmissions' => $recentSubmissions,
        ]);
    }
}
