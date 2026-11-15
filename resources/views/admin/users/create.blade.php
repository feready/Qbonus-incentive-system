
@extends('adminlte::page')

@section('title', 'Tambah User')

@section('content_header')
    <h1>Tambah Pengguna</h1>
@stop

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    @php
        $user = null;
        $isEdit = false;
    @endphp

    @include('admin.users.form')
</form>

@stop
