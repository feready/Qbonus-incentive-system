
<div class="card card-outline card-primary">
    <div class="card-body">

        <div class="form-group">
            <label>Nama Pengguna</label>

            <input
                type="text"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user?->name) }}"
                required
            >

            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Email</label>

            <input
                type="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user?->email) }}"
                required
            >

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Role Pengguna</label>

            <select
                name="role"
                class="form-control @error('role') is-invalid @enderror"
                required
            >
                <option value="USER"
                    @selected(old('role', $user?->role ?? 'USER') === 'USER')>
                    USER
                </option>

                <option value="SUPER_ADMIN"
                    @selected(old('role', $user?->role) === 'SUPER_ADMIN')>
                    SUPER ADMIN
                </option>
            </select>

            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            <small class="text-muted">
                Super Admin memiliki akses penuh untuk mengelola sistem.
            </small>
        </div>

        <hr>

        <div class="form-group">
            <label>
                Password
                @if($isEdit)
                    <span class="text-muted">(Opsional)</span>
                @endif
            </label>

            <input
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                autocomplete="new-password"
                minlength="12"
                @if(!$isEdit) required @endif
            >

            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            <small class="text-muted">
                Minimal 12 karakter.
                @if($isEdit)
                    Kosongkan jika tidak ingin mengganti password.
                @endif
            </small>
        </div>

        <div class="form-group">
            <label>Konfirmasi Password</label>

            <input
                type="password"
                name="password_confirmation"
                class="form-control"
                autocomplete="new-password"
                @if(!$isEdit) required @endif
            >
        </div>

    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>
            Simpan
        </button>

        <a href="{{ route('admin.users.index') }}"
           class="btn btn-secondary">
            Batal
        </a>
    </div>
</div>
