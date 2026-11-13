@extends('adminlte::page')

@section('title', 'Upload Bonus QBonus')

@section('content')

<div class="container-fluid">

    <h2 class="mb-4">
        <i class="fas fa-upload mr-2"></i>
        Upload Submission Bonus
    </h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali input berikut:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">Informasi Submission</h3>
        </div>

        <form action="{{ route('upload.process') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="card-body">

                <div class="form-group">
                    <label for="store_code">Nama Toko</label>

                    <select
                        name="store_code"
                        id="store_code"
                        class="form-control"
                        required
                    >
                        <option value="">-- Pilih Nama Toko --</option>

                        @foreach($stores as $store)
                            <option
                                value="{{ $store['code'] }}"
                                data-name="{{ $store['name'] }}"
                                {{ old('store_code') == $store['code'] ? 'selected' : '' }}
                            >
                                {{ $store['name'] }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="form-group">
                    <label for="store_code_display">Kode Toko</label>

                    <input
                        type="text"
                        id="store_code_display"
                        class="form-control"
                        readonly
                        placeholder="Kode toko otomatis"
                    >
                </div>

                <div class="form-group">
                    <label for="file">File Submission Excel</label>

                    <div class="custom-file">
                        <input
                            type="file"
                            name="file"
                            id="file"
                            class="custom-file-input"
                            accept=".xlsx,.xls,.xlsm"
                            required
                        >

                        <label class="custom-file-label" for="file">
                            Pilih file Excel
                        </label>
                    </div>

                    <small class="form-text text-muted">
                        Gunakan template resmi QBonus.
                    </small>
                </div>

            </div>

            <div class="card-footer">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calculator mr-1"></i>
                    Upload & Hitung Bonus
                </button>

                <a href="{{ route('template.download') }}"
                   class="btn btn-default">
                    <i class="fas fa-download mr-1"></i>
                    Download Template
                </a>

            </div>

        </form>

    </div>

</div>

@endsection

@section('js')
<script>
    const storeSelect = document.getElementById('store_code');
    const storeCodeDisplay = document.getElementById('store_code_display');

    function updateStoreCode() {
        const option = storeSelect.options[storeSelect.selectedIndex];

        storeCodeDisplay.value = option.dataset.name
            ? option.value
            : '';
    }

    storeSelect.addEventListener('change', updateStoreCode);

    updateStoreCode();

    document.getElementById('file').addEventListener('change', function () {
        const label = this.nextElementSibling;

        label.textContent = this.files.length
            ? this.files[0].name
            : 'Pilih file Excel';
    });
</script>
@endsection
