@extends('adminlte::page')

@section('title', 'Submission Summary')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-1">Submission Summary</h1>
            <p class="text-muted mb-0">
                Ringkasan hasil upload dan perbandingan insentif.
            </p>
        </div>

        <div class="d-flex flex-wrap">
            <a
                href="{{ route('bonus-submissions.export.excel') }}"
                class="btn btn-success mr-2 mb-2"
            >
                <i class="fas fa-file-excel mr-1"></i>
                Export Excel
            </a>

            <a
                href="{{ route('upload.index') }}"
                class="btn btn-primary mb-2"
            >
                <i class="fas fa-upload mr-1"></i>
                Upload Insentif
            </a>
        </div>
    </div>
@stop


@section('content')

<div class="container-fluid">

    {{-- SUMMARY CARDS --}}
    <div class="row">

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($summary['total_submissions']) }}</h3>
                    <p>Total Submission</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-upload"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['total_employees']) }}</h3>
                    <p>Total Karyawan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
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

        <div class="col-lg-2 col-md-4 col-sm-6">
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

        <div class="col-lg-2 col-md-4 col-sm-6">
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

    </div>


    {{-- SUBMISSION HISTORY --}}
    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Riwayat Submission
            </h3>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-bordered text-nowrap mb-0">

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

                    @forelse($submissions as $index => $submission)

                        @php
                            $status = strtoupper($submission->calculation_status ?? 'PENDING');
                        @endphp

                        <tr>
                            <td>
                                {{ $submissions->firstItem() + $index }}
                            </td>

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

                            <td title="{{ $submission->original_filename }}">
                                {{ \Illuminate\Support\Str::limit(
                                    $submission->original_filename,
                                    35
                                ) }}
                            </td>

                            <td class="text-center">
                                {{ number_format($submission->total_rows ?? 0) }}
                            </td>

                            <td class="text-center text-success">
                                <strong>
                                    {{ number_format($submission->match_count ?? 0) }}
                                </strong>
                            </td>

                            <td class="text-center text-warning">
                                <strong>
                                    {{ number_format($submission->difference_count ?? 0) }}
                                </strong>
                            </td>

                            <td class="text-center text-danger">
                                <strong>
                                    {{ number_format($submission->error_count ?? 0) }}
                                </strong>
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
                                <a
                                    href="{{ route('bonus-submissions.show', $submission->id) }}"
                                    class="btn btn-sm btn-info"
                                >
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center p-4">
                                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                <p class="mb-0">
                                    Belum ada submission yang diunggah.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($submissions->hasPages())
            <div class="card-footer clearfix">
                {{ $submissions->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>


    {{-- NON MATCH DETAILS --}}
    <div class="card card-outline card-warning">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Daftar Karyawan DIFFERENCE & ERROR
            </h3>

            <div class="card-tools">
                <span class="badge badge-warning">
                    Maksimal 100 data terbaru
                </span>
            </div>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-bordered text-nowrap mb-0">

                <thead class="bg-light">
                    <tr>
                        <th width="45">No</th>
                        <th>Tanggal</th>
                        <th>Store</th>
                        <th>Nama Karyawan</th>
                        <th>ID Karyawan</th>
                        <th>Nama File</th>
                        <th class="text-right">Excel Final</th>
                        <th class="text-right">QBonus Final</th>
                        <th class="text-right">Selisih</th>
                        <th>Status</th>
                        <th>Keputusan</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($nonMatches as $index => $item)

                        @php
                            $status = strtoupper($item->calculation_status ?? 'ERROR');
                            $decision = strtoupper($item->decision_status ?? 'PENDING');
                        @endphp

                        <tr class="{{ $status === 'ERROR' ? 'table-danger' : 'table-warning' }}">

                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $item->submission && $item->submission->created_at
                                    ? $item->submission->created_at->format('d/m/Y H:i')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $item->submission->store_name ?? '-' }}
                                <br>
                                <small class="text-muted">
                                    {{ $item->submission->store_code ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $item->employee_name ?? '-' }}</strong>
                            </td>

                            <td>{{ $item->employee_id ?? '-' }}</td>

                            <td title="{{ $item->submission->original_filename ?? '' }}">
                                {{ \Illuminate\Support\Str::limit(
                                    $item->submission->original_filename ?? '-',
                                    30
                                ) }}
                            </td>

                            <td class="text-right">
                                @if($item->excel_final !== null)
                                    Rp {{ number_format((float) $item->excel_final, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right">
                                @if($item->qbonus_final !== null)
                                    Rp {{ number_format((float) $item->qbonus_final, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-right">
                                @if($item->difference !== null)
                                    Rp {{ number_format((float) $item->difference, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if($status === 'DIFFERENCE')
                                    <span class="badge badge-warning">DIFFERENCE</span>
                                @else
                                    <span class="badge badge-danger">ERROR</span>
                                @endif
                            </td>

                            <td>
                                @if($decision === 'APPROVED')
                                    <span class="badge badge-success">APPROVED</span>
                                @elseif($decision === 'APPEALED')
                                    <span class="badge badge-info">APPEALED</span>
                                @elseif($decision === 'PENDING')
                                    <span class="badge badge-secondary">PENDING</span>
                                @else
                                    <span class="badge badge-light">
                                        {{ $decision }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a
                                    href="{{ route(
                                        'bonus-submissions.show',
                                        $item->bonus_submission_id
                                    ) }}"
                                    class="btn btn-sm btn-info"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="12" class="text-center p-4">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="mb-0">
                                    Belum ada data DIFFERENCE atau ERROR.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@stop
