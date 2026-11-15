
@extends('adminlte::page')

@section('title', 'Master File Insentif')

@section('content_header')
    <h1>Master File Insentif</h1>
    <p class="text-muted mb-0">
        Pengelolaan file referensi perhitungan KPI dan insentif QBonus.
    </p>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-1"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle mr-1"></i>
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan:</strong>

        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- ACTIVE MASTER FILE --}}

<div class="card card-outline card-success">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-database mr-1"></i>
            Master File Aktif
        </h3>

        <div class="card-tools">
            <span class="badge badge-success">ACTIVE</span>
        </div>
    </div>

    <div class="card-body">

        @if($activeVersion)

            <div class="row">

                <div class="col-md-8">

                    <h4>
                        Versi {{ $activeVersion->version_number }}
                    </h4>

                    <p class="mb-1">
                        <strong>Nama File:</strong>
                        {{ $activeVersion->original_filename }}
                    </p>

                    <p class="mb-1">
                        <strong>Ukuran:</strong>
                        {{ number_format($activeVersion->file_size / 1048576, 2) }}
                        MB
                    </p>

                    <p class="mb-1">
                        <strong>Diunggah oleh:</strong>
                        {{ $activeVersion->uploader->name ?? '-' }}
                    </p>

                    <p class="mb-1">
                        <strong>Diaktifkan oleh:</strong>
                        {{ $activeVersion->activator->name ?? '-' }}
                    </p>

                    <p class="mb-1">
                        <strong>Tanggal Aktivasi:</strong>
                        {{ $activeVersion->activated_at
                            ? $activeVersion->activated_at->format('d/m/Y H:i')
                            : '-' }}
                    </p>

                    <p class="mb-0">
                        <strong>SHA-256:</strong>
                        <small class="text-muted">
                            {{ $activeVersion->file_sha256 ?? '-' }}
                        </small>
                    </p>

                </div>

                <div class="col-md-4 text-md-right mt-3 mt-md-0">

                    <span class="badge badge-success p-2">
                        <i class="fas fa-check-circle mr-1"></i>
                        MASTER AKTIF
                    </span>

                    <p class="text-muted mt-2">
                        Semua pengajuan baru menggunakan versi ini.
                    </p>

                </div>

            </div>

        @else

            <div class="text-center p-3">
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>

                <h5 class="mt-2">
                    Belum ada Master File aktif
                </h5>

                <p class="text-muted mb-0">
                    Upload dan aktifkan file referensi untuk mengaktifkan
                    proses kalkulasi insentif.
                </p>
            </div>

        @endif

    </div>

</div>


{{-- UPLOAD MASTER FILE --}}

<div class="card card-outline card-primary">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-upload mr-1"></i>
            Upload Master File Baru
        </h3>
    </div>

    <form
        action="{{ route('admin.master-files.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @csrf

        <div class="card-body">

            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>

                File baru tidak langsung digunakan.
                Sistem akan memvalidasi file dan menyimpannya sebagai versi
                PENDING. Super Admin harus mengaktifkannya secara terpisah.
            </div>

            <div class="form-group">

                <label>File Master Insentif</label>

                <div class="custom-file">

                    <input
                        type="file"
                        name="file"
                        id="masterFile"
                        class="custom-file-input @error('file') is-invalid @enderror"
                        accept=".xls,.xlsx,.xlsm"
                        required
                    >

                    <label class="custom-file-label" for="masterFile">
                        Pilih file Excel...
                    </label>

                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <small class="form-text text-muted">
                    Format XLS, XLSX, atau XLSM. Maksimal 50 MB.
                </small>

            </div>

            <div class="form-group">

                <label>Catatan Versi</label>

                <textarea
                    name="notes"
                    rows="3"
                    maxlength="2000"
                    class="form-control @error('notes') is-invalid @enderror"
                    placeholder="Contoh: Master insentif periode Desember 2026..."
                >{{ old('notes') }}</textarea>

                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload mr-1"></i>
                Upload & Validasi
            </button>

        </div>

    </form>

</div>


{{-- REQUIRED SHEETS --}}

<div class="card card-outline card-info">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list-check mr-1"></i>
            Sheet Wajib Master File
        </h3>
    </div>

    <div class="card-body">

        <div class="row">

            @foreach($requiredSheets as $index => $sheet)

                <div class="col-lg-4 col-md-6 mb-2">

                    <span class="badge badge-light border mr-1">
                        {{ $index + 1 }}
                    </span>

                    {{ $sheet }}

                </div>

            @endforeach

        </div>

        <hr>

        <small class="text-muted">
            Semua nama sheet wajib tersedia. Tabel lookup yang digunakan
            kalkulasi juga harus dapat dibaca dan memiliki data.
        </small>

    </div>

</div>


{{-- VERSION HISTORY --}}

<div class="card card-outline card-dark">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-1"></i>
            Riwayat Master File
        </h3>
    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-bordered table-hover mb-0">

            <thead class="bg-light">
                <tr>
                    <th>No</th>
                    <th>Versi</th>
                    <th>Nama File</th>
                    <th>Ukuran</th>
                    <th>Diunggah Oleh</th>
                    <th>Tanggal Upload</th>
                    <th>Validasi</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($versions as $index => $version)

                    <tr>

                        <td>
                            {{ $versions->firstItem() + $index }}
                        </td>

                        <td>
                            <strong>
                                V{{ $version->version_number }}
                            </strong>
                        </td>

                        <td>
                            {{ $version->original_filename }}
                        </td>

                        <td>
                            {{ number_format($version->file_size / 1048576, 2) }}
                            MB
                        </td>

                        <td>
                            {{ $version->uploader->name ?? '-' }}
                        </td>

                        <td>
                            {{ $version->created_at
                                ? $version->created_at->format('d/m/Y H:i')
                                : '-' }}
                        </td>

                        <td>
                            @if($version->validation_status === 'VALID')
                                <span class="badge badge-success">VALID</span>
                            @else
                                <span class="badge badge-danger">
                                    {{ $version->validation_status }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($version->status === 'ACTIVE')
                                <span class="badge badge-success">ACTIVE</span>
                            @elseif($version->status === 'PENDING')
                                <span class="badge badge-warning">PENDING</span>
                            @elseif($version->status === 'ARCHIVED')
                                <span class="badge badge-secondary">ARCHIVED</span>
                            @else
                                <span class="badge badge-danger">
                                    {{ $version->status }}
                                </span>
                            @endif
                        </td>

                        <td>
                            {{ $version->notes ?: '-' }}

                            @if(
                                $version->validation_status !== 'VALID' &&
                                !empty($version->validation_summary['message'])
                            )
                                <br>
                                <small class="text-danger">
                                    {{ $version->validation_summary['message'] }}
                                </small>
                            @endif
                        </td>

                        <td class="text-center">

                            @if(
                                $version->validation_status === 'VALID' &&
                                !$version->is_active
                            )

                                <form
                                    action="{{ route(
                                        'admin.master-files.activate',
                                        $version->id
                                    ) }}"
                                    method="POST"
                                    onsubmit="return confirm(
                                        'Aktifkan Master File versi {{ $version->version_number }}? Versi yang saat ini aktif akan menjadi arsip.'
                                    )"
                                >

                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-success"
                                    >
                                        <i class="fas fa-check mr-1"></i>
                                        Aktifkan
                                    </button>

                                </form>

                            @elseif($version->is_active)

                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Aktif
                                </span>

                            @else

                                <span class="text-muted">-</span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10" class="text-center p-4">
                            Belum ada riwayat Master File.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($versions->hasPages())
        <div class="card-footer">
            {{ $versions->links('pagination::bootstrap-4') }}
        </div>
    @endif

</div>

@stop

@section('js')
<script>
    $('#masterFile').on('change', function () {
        const fileName = this.files.length
            ? this.files[0].name
            : 'Pilih file Excel...';

        $(this).next('.custom-file-label').text(fileName);
    });
</script>
@stop
