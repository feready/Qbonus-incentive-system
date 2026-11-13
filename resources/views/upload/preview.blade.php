
@extends('adminlte::page')

@section('title', 'QBonus - Hasil Kalkulasi')

@section('content')

@php
    $sourceHeaders = $rows->isNotEmpty()
        ? collect($rows->first())
            ->map(fn($v) => trim((string) $v))
            ->values()
            ->toArray()
        : [];

    $headerIndexes = array_flip($sourceHeaders);

    $displayHeaders = [
        'Bln', 'ID', 'Name', 'Join Date', 'C_Cat',
        'Ach Zona', 'Ach Cat', 'Ach PB', 'Ach DSL',
        'Ach AWT', 'Ach NEMS',
        'KPI Zona', 'KPI Cat', 'KPI PB', 'KPI DSL',
        'KPI AWT', 'KPI NEMS', 'Total KPI',
        'Tier Ach', 'Insentif Basic',
        'Ins Basic x KPI', 'Final Insentif',
    ];

    $percentColumns = [
        'Ach Zona', 'Ach Cat', 'Ach PB',
        'Ach DSL', 'Ach AWT', 'Ach NEMS',
        'KPI Zona', 'KPI Cat', 'KPI PB',
        'KPI DSL', 'KPI AWT', 'KPI NEMS',
        'Total KPI',
    ];

    $moneyColumns = [
        'Insentif Basic', 'Ins Basic x KPI', 'Final Insentif',
    ];

    $summary = $calculationSummary ?? [
        'match' => 0,
        'difference' => 0,
        'error' => 0,
    ];
@endphp

<div class="container-fluid">

    <h2 class="mb-4">
        <i class="fas fa-calculator mr-2"></i>
        Hasil Kalkulasi QBonus
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @isset($submission)
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Nama Toko</strong>
                        <div>{{ $submission->store_name }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Kode Toko</strong>
                        <div>{{ $submission->store_code }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>File</strong>
                        <div>{{ $submission->original_filename }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Submission</strong>
                        <div>#{{ $submission->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    {{-- SUMMARY --}}

    <div class="row">

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['match'] }}</h3>
                    <p>MATCH</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['difference'] }}</h3>
                    <p>DIFFERENCE</p>
                </div>
                <div class="icon">
                    <i class="fas fa-not-equal"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['error'] }}</h3>
                    <p>ERROR</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- TABEL RINGKAS --}}

    <div class="card card-outline card-primary">

        <div class="card-header">
            <h3 class="card-title">Detail Perbandingan Karyawan</h3>
        </div>

        <div class="card-body">

            @foreach($validation['rows'] ?? [] as $validatedRow)

                @php
                    $getValue = function ($name) use ($validatedRow, $headerIndexes) {
                        $index = $headerIndexes[$name] ?? null;
                        return $index !== null
                            ? ($validatedRow['data'][$index] ?? null)
                            : null;
                    };

                    $comparison = $validatedRow['comparison'] ?? 'ERROR';
                    $decision = $validatedRow['decision_status'] ?? 'NOT_REQUIRED';
                    $itemId = $validatedRow['item_id'] ?? null;
                @endphp

                <div class="border rounded mb-3 p-3">

                    {{-- IDENTITAS KARYAWAN --}}

                    <div class="d-flex justify-content-between align-items-start flex-wrap">

                        <div>
                            <h5 class="mb-1">
                                {{ $getValue('Name') ?? 'Tanpa Nama' }}
                            </h5>

                            <small class="text-muted">
                                ID: {{ $getValue('ID') ?? '-' }}
                                &nbsp; | &nbsp;
                                Code: {{ $getValue('Code') ?? '-' }}
                            </small>
                        </div>

                        <div class="text-right">
                            @if($comparison === 'MATCH')
                                <span class="badge badge-success">MATCH</span>
                            @elseif($comparison === 'DIFFERENCE')
                                <span class="badge badge-warning">DIFFERENCE</span>
                            @else
                                <span class="badge badge-danger">ERROR</span>
                            @endif
                        </div>

                    </div>

                    <hr>

                    {{-- PERBANDINGAN NILAI --}}

                    <div class="row">

                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted">Final Insentif Excel</small>
                            <div class="font-weight-bold">
                                @if(isset($validatedRow['excel_final']))
                                    Rp {{ number_format($validatedRow['excel_final'], 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted">Final Insentif QBonus</small>
                            <div class="font-weight-bold">
                                @if(isset($validatedRow['calculation']['Final Insentif QBonus']))
                                    Rp {{ number_format($validatedRow['calculation']['Final Insentif QBonus'], 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted">Selisih</small>
                            <div class="font-weight-bold">
                                @if(isset($validatedRow['selisih']))
                                    Rp {{ number_format(abs($validatedRow['selisih']), 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted">Decision Status</small>
                            <div>
                                @if($comparison === 'MATCH')
                                    <span class="badge badge-secondary">NOT REQUIRED</span>
                                @elseif($comparison === 'ERROR')
                                    <span class="badge badge-danger">NEED CHECK</span>
                                @elseif($decision === 'APPROVED')
                                    <span class="badge badge-success">APPROVED</span>
                                @elseif($decision === 'APPEALED')
                                    <span class="badge badge-warning">APPEALED</span>
                                @else
                                    <span class="badge badge-secondary">PENDING</span>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- ACTION HANYA DIFFERENCE --}}

                    @if($comparison === 'DIFFERENCE' && $decision === 'PENDING' && $itemId)

                        <hr>

                        <div class="row">

                            <div class="col-md-3 mb-2">

                                <form
                                    action="{{ route('bonus-submission-items.approve', $itemId) }}"
                                    method="POST"
                                    onsubmit="return confirm('Approve hasil karyawan ini?')"
                                >
                                    @csrf

                                    <button class="btn btn-success btn-sm btn-block" type="submit">
                                        <i class="fas fa-check mr-1"></i>
                                        APPROVE
                                    </button>

                                </form>

                            </div>

                            <div class="col-md-9">

                                <details>
                                    <summary class="btn btn-warning btn-sm">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        APPEAL
                                    </summary>

                                    <form
                                        action="{{ route('bonus-submission-items.appeal', $itemId) }}"
                                        method="POST"
                                        class="mt-2"
                                    >
                                        @csrf

                                        <textarea
                                            name="appeal_reason"
                                            class="form-control"
                                            rows="2"
                                            minlength="5"
                                            maxlength="5000"
                                            required
                                            placeholder="Tuliskan alasan appeal untuk karyawan ini..."
                                        ></textarea>

                                        <button
                                            type="submit"
                                            class="btn btn-warning btn-sm mt-2"
                                            onclick="return confirm('Kirim appeal untuk karyawan ini?')"
                                        >
                                            SUBMIT APPEAL
                                        </button>
                                    </form>
                                </details>

                            </div>

                        </div>

                    @elseif($decision === 'APPROVED')

                        <hr>
                        <span class="text-success">
                            <i class="fas fa-check-circle"></i>
                            Karyawan ini sudah di-approve.
                        </span>

                    @elseif($decision === 'APPEALED')

                        <hr>
                        <span class="text-warning">
                            <i class="fas fa-exclamation-circle"></i>
                            Karyawan ini sudah di-appeal.
                        </span>

                        <div class="mt-2">
                            <small>
                                <strong>Reason:</strong>
                                {{ $validatedRow['appeal_reason'] ?? '-' }}
                            </small>
                        </div>

                    @elseif($comparison === 'MATCH')

                        <small class="text-success">
                            <i class="fas fa-check"></i>
                            Tidak memerlukan tindakan.
                        </small>

                    @else

                        <small class="text-danger">
                            Perlu pemeriksaan data sebelum keputusan.
                        </small>

                    @endif

                    {{-- DETAIL 22 KOLOM --}}

                    <hr>

                    <details>
                        <summary class="text-primary" style="cursor:pointer">
                            <i class="fas fa-list-alt mr-1"></i>
                            Lihat Detail KPI dan Insentif
                        </summary>

                        <div class="table-responsive mt-3">

                            <table class="table table-sm table-bordered">

                                <tbody>

                                    @foreach($displayHeaders as $header)

                                        @php
                                            $value = $getValue($header);
                                        @endphp

                                        <tr>
                                            <th style="width:220px">
                                                {{ $header }}
                                            </th>

                                            <td>

                                                @if($value !== null && $value !== '')

                                                    @if(in_array($header, $percentColumns) && is_numeric($value))

                                                        {{ number_format((float) $value * 100, 2, ',', '.') }}%

                                                    @elseif(in_array($header, $moneyColumns) && is_numeric($value))

                                                        Rp {{ number_format((float) $value, 2, ',', '.') }}

                                                    @else

                                                        {{ $value }}

                                                    @endif

                                                @else
                                                    -
                                                @endif

                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        @if(!empty($validatedRow['errors']))
                            <div class="alert alert-danger">
                                @foreach($validatedRow['errors'] as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($validatedRow['warnings']))
                            <div class="alert alert-warning">
                                @foreach($validatedRow['warnings'] as $warning)
                                    <div>{{ $warning }}</div>
                                @endforeach
                            </div>
                        @endif

                    </details>

                </div>

            @endforeach

        </div>

    </div>

</div>

@endsection
