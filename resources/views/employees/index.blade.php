@extends('adminlte::page')

@section('title', 'Upload Insentif')

@section('content_header')
<h1>Upload Insentif</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <h4>Upload File Excel Insentif</h4>

        <form>

            <div class="mb-3">
                <label class="form-label">
                    Pilih File
                </label>

                <input
                    type="file"
                    class="form-control">
            </div>

            <button class="btn btn-success">
                Upload
            </button>

        </form>

    </div>
</div>

@stop