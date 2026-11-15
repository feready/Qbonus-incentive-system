
@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('content_header')
    <h1>Super Admin Dashboard</h1>
    <p class="text-muted">
        Monitoring keseluruhan sistem QBonus.
    </p>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($summary['total_users']) }}</h3>
                <p>Total User</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>{{ number_format($summary['total_super_admins']) }}</h3>
                <p>Super Admin</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($summary['total_submissions']) }}</h3>
                <p>Total Submission</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-upload"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($summary['total_employees']) }}</h3>
                <p>Total Karyawan Diproses</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-friends"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($summary['match']) }}</h3>
                <p>MATCH</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($summary['difference']) }}</h3>
                <p>DIFFERENCE</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($summary['error']) }}</h3>
                <p>ERROR</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-orange">
            <div class="inner">
                <h3>{{ number_format($summary['pending_decisions']) }}</h3>
                <p>Menunggu Keputusan</p>
            </div>
            <div class="icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>

</div>

<div class="card card-outline card-primary">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-1"></i>
            10 Submission Terbaru Seluruh Pengguna
        </h3>
    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover table-bordered mb-0">

            <thead class="bg-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pengguna</th>
                    <th>Store</th>
                    <th>Nama File</th>
                    <th>Total</th>
                    <th>MATCH</th>
                    <th>DIFFERENCE</th>
                    <th>ERROR</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recentSubmissions as $index => $submission)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            {{ $submission->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            {{ $submission->user->name ?? '-' }}
                        </td>

                        <td>
                            {{ $submission->store_name ?? '-' }}
                        </td>

                        <td>
                            {{ \Illuminate\Support\Str::limit(
                                $submission->original_filename,
                                35
                            ) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($submission->total_rows ?? 0) }}
                        </td>

                        <td class="text-center text-success">
                            {{ number_format($submission->match_count ?? 0) }}
                        </td>

                        <td class="text-center text-warning">
                            {{ number_format($submission->difference_count ?? 0) }}
                        </td>

                        <td class="text-center text-danger">
                            {{ number_format($submission->error_count ?? 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center p-4">
                            Belum ada submission dari pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@stop
