<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Daftar seluruh pengguna.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->withCount('bonusSubmissions')
            ->latest();

        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $role = $request->query('role');

        if (in_array($role, [
            User::ROLE_USER,
            User::ROLE_SUPER_ADMIN,
        ], true)) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15)->withQueryString();

        $summary = [
            'users' => User::where(
                'role',
                User::ROLE_USER
            )->count(),

            'super_admins' => User::where(
                'role',
                User::ROLE_SUPER_ADMIN
            )->count(),

            'total' => User::count(),
        ];

        return view('admin.users.index', compact(
            'users',
            'summary',
            'search',
            'role'
        ));
    }

    /**
     * Form tambah pengguna.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_USER,
                    User::ROLE_SUPER_ADMIN,
                ]),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        // Role ditetapkan secara eksplisit, bukan mass assignment.
        $user->role = $validated['role'];
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Form edit pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:12',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_USER,
                    User::ROLE_SUPER_ADMIN,
                ]),
            ],
        ]);

        // Tidak boleh menurunkan role akun yang sedang login.
        if (
            (int) $user->id === (int) auth()->id() &&
            $validated['role'] !== User::ROLE_SUPER_ADMIN
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Kamu tidak dapat mengubah role akun Super Admin yang sedang digunakan.',
                ]);
        }

        // Jangan sampai tidak ada Super Admin yang tersisa.
        if (
            $user->isSuperAdmin() &&
            $validated['role'] === User::ROLE_USER &&
            User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Tidak dapat mengubah Super Admin terakhir menjadi User biasa.',
                ]);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }
}
