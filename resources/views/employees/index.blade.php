@extends('adminlte::page')

@section('title', 'Data Karyawan')

@section('content_header')
<h1>Data Karyawan</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">

        <h3 class="card-title">Master Karyawan</h3>

    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <thead>

                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Divisi</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

                @forelse($employees as $employee)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $employee->nik }}</td>

                    <td>{{ $employee->name }}</td>

                    <td>{{ $employee->division }}</td>

                    <td>{{ $employee->position }}</td>

                    <td>
                        @if($employee->active)
                            Aktif
                        @else
                            Non Aktif
                        @endif
                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="6" class="text-center">
                        Belum ada data.
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@stop