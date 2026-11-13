@extends('layouts.guest')

@section('title', 'Login - QBonus')

@section('content')

<div class="min-h-screen flex">

    @include('components.login-left')

    @include('components.login-form')

</div>

@endsection