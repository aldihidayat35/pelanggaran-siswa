<?php

namespace Database\Seeders;

use App\Models\KategoriPelanggaran;
use Illuminate\Database\Seeder;

class KategoriPelanggaranSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'Kedisiplinan',
                'deskripsi' => 'Pelanggaran terkait kedisiplinan waktu dan kehadiran',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Kerapian',
                'deskripsi' => 'Pelanggaran terkait kerapian dan kepatuhan atribut',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Kehadiran',
                'deskripsi' => 'Pelanggaran terkait kehadiran dan ketidakhadiran',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Etika',
                'deskripsi' => 'Pelanggaran terkait sopan santun dan tata krama',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Keamanan',
                'deskripsi' => 'Pelanggaran terkait keamanan sekolah dan lingkungan',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Pelanggaran Berat',
                'deskripsi' => 'Pelanggaran tingkat berat yang memerlukan tindakan khusus',
                'status' => 'Aktif',
            ],
        ];

        foreach ($categories as $category) {
            KategoriPelanggaran::create($category);
        }
    }
}
