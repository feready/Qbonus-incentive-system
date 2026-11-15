
@extends('adminlte::page')

@section('title', 'Login Activity')

@section('content_header')
    <h1>Login Activity & Audit Log</h1>
    <p class="text-muted">
        Monitoring riwayat aktivitas pengguna QBonus.
    </p>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($summary['total']) }}</h3>
                <p>Total Aktivitas</p>
            </div>
            <div class="icon">
                <i class="fas fa-history"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($summary['login_success']) }}</h3>
                <p>Login Berhasil</p>
            </div>
            <div class="icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($summary['login_failed']) }}</h3>
                <p>Login Gagal</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($summary['logout']) }}</h3>
                <p>Logout</p>
            </div>
            <div class="icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
    </div>

</div>


<div class="card card-outline card-primary">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Riwayat Aktivitas Pengguna
        </h3>
    </div>

    <div class="card-body">

        <form method="GET"
              action="{{ route('admin.audit-logs.index') }}"
              class="row mb-3">

            <div class="col-md-5 mb-2">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nama, email, atau IP..."
                    value="{{ $search }}"
                >
            </div>

            <div class="col-md-4 mb-2">
                <select name="event" class="form-control">
                    <option value="">Semua Aktivitas</option>

                    @foreach($eventOptions as $key => $label)
                        <option
                            value="{{ $key }}"
                            @selected($event === $key)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i>
                    Cari
                </button>

                <a
                    href="{{ route('admin.audit-logs.index') }}"
                    class="btn btn-secondary"
                >
                    Reset
                </a>
            </div>

        </form>


        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="bg-light">
                    <tr>
                        <th width="45">No</th>
                        <th>Tanggal & Waktu</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                        <th>IP Address</th>
                        <th>Lokasi</th>
                        <th>Browser / Device</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($logs as $index => $log)

                        @php
                            $badges = [
                                'LOGIN_SUCCESS' => 'success',
                                'LOGIN_FAILED' => 'danger',
                                'LOGOUT' => 'secondary',
                                'SUBMISSION_UPLOADED' => 'primary',
                                'EMPLOYEE_APPROVED' => 'success',
                                'EMPLOYEE_APPEALED' => 'info',
                                'USER_CREATED' => 'primary',
                                'USER_UPDATED' => 'warning',
                                'MASTER_FILE_UPLOADED' => 'primary',
                                'MASTER_FILE_ACTIVATED' => 'success',
                            ];

                            $labels = [
                                'LOGIN_SUCCESS' => 'LOGIN BERHASIL',
                                'LOGIN_FAILED' => 'LOGIN GAGAL',
                                'LOGOUT' => 'LOGOUT',
                                'SUBMISSION_UPLOADED' => 'UPLOAD SUBMISSION',
                                'EMPLOYEE_APPROVED' => 'APPROVE KARYAWAN',
                                'EMPLOYEE_APPEALED' => 'APPEAL KARYAWAN',
                                'USER_CREATED' => 'TAMBAH USER',
                                'USER_UPDATED' => 'EDIT USER',
                                'MASTER_FILE_UPLOADED' => 'UPLOAD MASTER',
                                'MASTER_FILE_ACTIVATED' => 'AKTIVASI MASTER',
                            ];

                            $badge = $badges[$log->event] ?? 'dark';
                            $label = $labels[$log->event] ?? $log->event;

                            $ip = $log->ip_address ?? '-';

                            $location = in_array($ip, [
                                '127.0.0.1',
                                '::1',
                                'localhost',
                            ], true)
                                ? 'Localhost'
                                : 'Belum dipetakan';

                            $browser = $log->user_agent
                                ? \Illuminate\Support\Str::limit(
                                    $log->user_agent,
                                    65
                                )
                                : '-';
                        @endphp

                        <tr>

                            <td>
                                {{ $logs->firstItem() + $index }}
                            </td>

                            <td>
                                {{ $log->created_at
                                    ? $log->created_at->format('d/m/Y H:i:s')
                                    : '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $log->user_name ?? 'Pengguna tidak dikenal' }}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    {{ $log->user_email ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge badge-{{ $badge }}">
                                    {{ $label }}
                                </span>

                                <br>

                                <small class="text-muted">
                                    {{ $log->description }}
                                </small>
                            </td>

                            <td>
                                <code>{{ $ip }}</code>
                            </td>

                            <td>
                                {{ $location }}
                            </td>

                            <td title="{{ $log->user_agent }}">
                                <small>{{ $browser }}</small>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center p-4">
                                <i class="fas fa-history fa-2x text-muted mb-2"></i>

                                <p class="mb-0">
                                    Belum ada aktivitas tercatat.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $logs->links('pagination::bootstrap-4') }}
        </div>

    </div>

</div>

@stop
