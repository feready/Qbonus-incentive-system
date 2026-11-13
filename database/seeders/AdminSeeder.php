<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('qbonus.admin.email');
        $password = config('qbonus.admin.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException(
                'QB_ADMIN_EMAIL dan QB_ADMIN_PASSWORD wajib diisi di file .env.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config(
                    'qbonus.admin.name',
                    'Super Administrator'
                ),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
