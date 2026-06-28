<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'guru@guru.com'],
            [
                'name' => 'Guru Piket',
                'nip' => 'GURU-001',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'no_hp' => '081234567890',
                'jabatan' => 'Guru Piket',
                'is_active' => true,
            ]
        );
    }
}
