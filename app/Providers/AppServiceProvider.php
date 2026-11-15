<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Hak akses Super Admin.
        Gate::define('superadmin', function (User $user) {
            return $user->isSuperAdmin();
        });

        // Login berhasil.
        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user instanceof User
                ? $event->user
                : null;

            app(AuditLogger::class)->log(
                'LOGIN_SUCCESS',
                'Pengguna berhasil login.',
                $user
            );
        });

        // Logout.
        Event::listen(Logout::class, function (Logout $event): void {
            $user = $event->user instanceof User
                ? $event->user
                : null;

            app(AuditLogger::class)->log(
                'LOGOUT',
                'Pengguna berhasil logout.',
                $user
            );
        });

        // Login gagal.
        Event::listen(Failed::class, function (Failed $event): void {
            $user = $event->user instanceof User
                ? $event->user
                : null;

            $email = $event->credentials['email']
                ?? $event->credentials['username']
                ?? null;

            app(AuditLogger::class)->log(
                'LOGIN_FAILED',
                'Percobaan login gagal.',
                $user,
                [],
                is_string($email) ? $email : null
            );
        });
    }
}
