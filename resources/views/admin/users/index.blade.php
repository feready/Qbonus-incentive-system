
@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>User Management</h1>
            <p class="text-muted mb-0">
                Pengelolaan akun pengguna QBonus.
            </p>
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="btn btn-primary">
            <i class="fas fa-user-plus mr-1"></i>
            Tambah User
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">

    <div class="col-md-4">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($summary['total']) }}</h3>
                <p>Total Akun</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($summary['users']) }}</h3>
                <p>User Biasa</p>
            </div>
            <div class="icon">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>{{ number_format($summary['super_admins']) }}</h3>
                <p>Super Admin</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>

</div>

<div class="card card-outline card-primary">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users mr-1"></i>
            Daftar Pengguna
        </h3>
    </div>

    <div class="card-body">

        <form method="GET"
              action="{{ route('admin.users.index') }}"
              class="row mb-3">

            <div class="col-md-5 mb-2">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nama atau email..."
                    value="{{ $search }}"
                >
            </div>

            <div class="col-md-3 mb-2">
                <select name="role" class="form-control">
                    <option value="">Semua Role</option>

                    <option value="USER"
                        @selected($role === 'USER')>
                        USER
                    </option>

                    <option value="SUPER_ADMIN"
                        @selected($role === 'SUPER_ADMIN')>
                        SUPER ADMIN
                    </option>
                </select>
            </div>

            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i>
                    Cari
                </button>

                <a href="{{ route('admin.users.index') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Submission</th>
                        <th>Tanggal Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $index => $user)

                        <tr>
                            <td>
                                {{ $users->firstItem() + $index }}
                            </td>

                            <td>
                                <strong>{{ $user->name }}</strong>

                                @if($user->id === auth()->id())
                                    <span class="badge badge-info">
                                        Anda
                                    </span>
                                @endif
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge badge-dark">
                                        SUPER ADMIN
                                    </span>
                                @else
                                    <span class="badge badge-primary">
                                        USER
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{ number_format($user->bonus_submissions_count) }}
                            </td>

                            <td>
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.users.edit', $user->id) }}"
                                    class="btn btn-sm btn-warning"
                                >
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center p-4">
                                Belum ada data pengguna.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>

    </div>

</div>

@stop
