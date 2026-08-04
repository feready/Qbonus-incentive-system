<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use Illuminate\Support\Str;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['code' => 'OPS', 'name' => 'Operasional'],
            ['code' => 'FIN', 'name' => 'Finance'],
            ['code' => 'MKT', 'name' => 'Marketing'],
            ['code' => 'HRD', 'name' => 'Human Resource'],
            ['code' => 'ADM', 'name' => 'Administrasi'],
        ];

        foreach ($divisions as $division) {
            Division::create([
                'uuid' => Str::uuid(),
                'code' => $division['code'],
                'name' => $division['name'],
                'description' => null,
                'active' => true,
            ]);
        }
    }
}