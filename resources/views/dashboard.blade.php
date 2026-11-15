@extends('adminlte::page')

@section('title', 'Dashboard QBonus')

@section('content_header')
    <div class="mb-3">
        <h1>Dashboard QBonus</h1>

        <p class="text-muted mb-0">
            Selamat datang,
            <strong>{{ auth()->user()->name }}</strong>.
            Berikut ringkasan aktivitas pengajuan insentif kamu.
        </p>
    </div>
@stop

@section('content')

<div class="container-fluid">

    {{-- PERIODE --}}
    <div class="mb-3">
        <span class="text-muted">
            <i class="far fa-calendar-alt mr-1"></i>
            Periode: {{ $currentMonth }}
        </span>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row">

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($summary['total_submissions']) }}</h3>
                    <p>Submission Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Lihat Submission
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['total_employees']) }}</h3>
                    <p>Karyawan Diproses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Lihat Detail
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['match']) }}</h3>
                    <p>MATCH</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Lihat Hasil
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($summary['difference']) }}</h3>
                    <p>DIFFERENCE</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Periksa Perbedaan
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($summary['error']) }}</h3>
                    <p>ERROR</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Periksa Error
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($summary['pending_decisions']) }}</h3>
                    <p>Menunggu Keputusan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <a href="{{ route('bonus-submissions.index') }}"
                   class="small-box-footer">
                    Lihat Submission
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>


    {{-- QUICK ACCESS --}}
    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt mr-1"></i>
                Akses Cepat
            </h3>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-lg-4 col-md-6 mb-2">
                    <a href="{{ route('upload.index') }}"
                       class="btn btn-primary btn-block">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Insentif
                    </a>
                </div>

                <div class="col-lg-4 col-md-6 mb-2">
                    <a href="{{ route('bonus-submissions.index') }}"
                       class="btn btn-info btn-block">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Submission Summary
                    </a>
                </div>

                <div class="col-lg-4 col-md-6 mb-2">
                    <a href="{{ route('template.download') }}"
                       class="btn btn-success btn-block">
                        <i class="fas fa-file-excel mr-2"></i>
                        Download Template
                    </a>
                </div>

            </div>

        </div>

    </div>


    {{-- RECENT SUBMISSIONS --}}
    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Submission Terbaru
            </h3>

            <div class="card-tools">
                <a href="{{ route('bonus-submissions.index') }}"
                   class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-bordered mb-0">

                <thead class="bg-light">
                    <tr>
                        <th width="45">No</th>
                        <th>Tanggal Upload</th>
                        <th>Store</th>
                        <th>Nama File</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">MATCH</th>
                        <th class="text-center">DIFFERENCE</th>
                        <th class="text-center">ERROR</th>
                        <th>Status</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($recentSubmissions as $index => $submission)

                        @php
                            $status = strtoupper(
                                $submission->calculation_status ?? 'PENDING'
                            );
                        @endphp

                        <tr>

                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $submission->created_at
                                    ? $submission->created_at->format('d/m/Y H:i')
                                    : '-' }}
                            </td>

                            <td>
                                <strong>{{ $submission->store_name ?? '-' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $submission->store_code ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ \Illuminate\Support\Str::limit(
                                    $submission->original_filename ?? '-',
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

                            <td>
                                @if($status === 'MATCH')
                                    <span class="badge badge-success">MATCH</span>
                                @elseif($status === 'DIFFERENCE')
                                    <span class="badge badge-warning">DIFFERENCE</span>
                                @elseif($status === 'ERROR')
                                    <span class="badge badge-danger">ERROR</span>
                                @else
                                    <span class="badge badge-secondary">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('bonus-submissions.show', $submission->id) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center p-4">
                                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>

                                <p class="mb-2">
                                    Belum ada submission pada akun kamu.
                                </p>

                                <a href="{{ route('upload.index') }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload mr-1"></i>
                                    Mulai Upload Insentif
                                </a>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@stop
