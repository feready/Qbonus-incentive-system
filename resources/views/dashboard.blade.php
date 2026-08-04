@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard QBonus</h1>
@stop

@section('content')
<div class="row">

    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>0</h3>
                <p>Karyawan</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>0</h3>
                <p>Bonus Bulan Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill"></i>
            </div>
        </div>
    </div>

</div>
@stop