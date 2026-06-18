<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create sample user
        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'User Biasa',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Seed app settings
        $this->call(AppSettingSeeder::class);

        // Seed pelanggaran data
        $this->call([
            KategoriPelanggaranSeeder::class,
            PelanggaranSeeder::class,
            SiswaSeeder::class,
            PelanggaranSiswaSeeder::class,
        ]);
    }
}
