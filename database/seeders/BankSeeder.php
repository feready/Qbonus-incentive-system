<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['code' => 'BCA',  'name' => 'Bank Central Asia'],
            ['code' => 'BRI',  'name' => 'Bank Rakyat Indonesia'],
            ['code' => 'BNI',  'name' => 'Bank Negara Indonesia'],
            ['code' => 'BMRI', 'name' => 'Bank Mandiri'],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga'],
        ];

        foreach ($banks as $bank) {
            Bank::firstOrCreate(
                ['code' => $bank['code']],
                [
                    'uuid'   => Str::uuid(),
                    'name'   => $bank['name'],
                    'active' => true,
                ]
            );
        }
    }
}