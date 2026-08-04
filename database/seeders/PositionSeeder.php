<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['code' => 'STF', 'name' => 'Staff'],
            ['code' => 'TL', 'name' => 'Team Leader'],
            ['code' => 'DSM', 'name' => 'Deputy Store Manager'],
            ['code' => 'SM',  'name' => 'Store Manager'],
            ['code' => 'DIR', 'name' => 'Direktur'],
        ];

        foreach ($positions as $position) {
            Position::create([
                'uuid' => Str::uuid(),
                'code' => $position['code'],
                'name' => $position['name'],
                'description' => null,
                'active' => true,
            ]);
        }
    }
}