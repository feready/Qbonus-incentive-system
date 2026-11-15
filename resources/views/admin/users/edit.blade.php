
@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit Pengguna</h1>
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

<form action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    @php
        $isEdit = true;
    @endphp

    @include('admin.users.form')
</form>

@stop
