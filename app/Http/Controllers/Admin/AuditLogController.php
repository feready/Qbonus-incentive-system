<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $event = (string) $request->query('event', '');

        $eventOptions = [
            'LOGIN_SUCCESS' => 'Login Berhasil',
            'LOGIN_FAILED' => 'Login Gagal',
            'LOGOUT' => 'Logout',
            'SUBMISSION_UPLOADED' => 'Upload Submission',
            'EMPLOYEE_APPROVED' => 'Approve Karyawan',
            'EMPLOYEE_APPEALED' => 'Appeal Karyawan',
            'USER_CREATED' => 'Tambah User',
            'USER_UPDATED' => 'Edit User',
            'MASTER_FILE_UPLOADED' => 'Upload Master File',
            'MASTER_FILE_ACTIVATED' => 'Aktivasi Master File',
        ];

        $query = AuditLog::query()->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (array_key_exists($event, $eventOptions)) {
            $query->where('event', $event);
        }

        $logs = $query
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total' => AuditLog::count(),

            'login_success' => AuditLog::where(
                'event',
                'LOGIN_SUCCESS'
            )->count(),

            'login_failed' => AuditLog::where(
                'event',
                'LOGIN_FAILED'
            )->count(),

            'logout' => AuditLog::where(
                'event',
                'LOGOUT'
            )->count(),
        ];

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'summary' => $summary,
            'search' => $search,
            'event' => $event,
            'eventOptions' => $eventOptions,
        ]);
    }
}
