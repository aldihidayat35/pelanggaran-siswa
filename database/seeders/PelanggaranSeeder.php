<?php

namespace Database\Seeders;

use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use Illuminate\Database\Seeder;

class PelanggaranSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggaranList = [
            [
                'kode_pelanggaran' => 'KD001',
                'nama_pelanggaran' => 'Terlambat masuk sekolah',
                'kategori' => 'Kedisiplinan',
                'tingkat' => 'Ringan',
                'poin' => 5,
                'deskripsi' => 'Siswa tidak masuk sekolah tepat waktu pada jam pelajaran pertama',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'KR001',
                'nama_pelanggaran' => 'Tidak memakai atribut lengkap',
                'kategori' => 'Kerapian',
                'tingkat' => 'Ringan',
                'poin' => 10,
                'deskripsi' => 'Siswa tidak menggunakan atribut sekolah sesuai ketentuan (topi, dasi, scarf, dll)',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'KH001',
                'nama_pelanggaran' => 'Membolos',
                'kategori' => 'Kehadiran',
                'tingkat' => 'Sedang',
                'poin' => 25,
                'deskripsi' => 'Siswa meninggalkan area sekolah tanpa izin selama jam pelajaran berlangsung',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'ET001',
                'nama_pelanggaran' => 'Berkata kasar kepada guru/teman',
                'kategori' => 'Etika',
                'tingkat' => 'Sedang',
                'poin' => 20,
                'deskripsi' => 'Siswa menggunakan kata-kata tidak pantas atau merendahkan kepada guru maupun teman',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'PB001',
                'nama_pelanggaran' => 'Merokok di lingkungan sekolah',
                'kategori' => 'Pelanggaran Berat',
                'tingkat' => 'Berat',
                'poin' => 50,
                'deskripsi' => 'Siswa terdeteksi merokok di area lingkungan sekolah',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'PB002',
                'nama_pelanggaran' => 'Berkelahi',
                'kategori' => 'Pelanggaran Berat',
                'tingkat' => 'Berat',
                'poin' => 75,
                'deskripsi' => 'Siswa terlibat dalam perkelahian di lingkungan sekolah',
                'status' => 'Aktif',
            ],
            [
                'kode_pelanggaran' => 'KM001',
                'nama_pelanggaran' => 'Membawa benda berbahaya',
                'kategori' => 'Keamanan',
                'tingkat' => 'Berat',
                'poin' => 100,
                'deskripsi' => 'Siswa membawa benda berbahaya (senjata tajam, senjata listrik, dll) ke sekolah',
                'status' => 'Aktif',
            ],
        ];

        foreach ($pelanggaranList as $item) {
            $kategori = KategoriPelanggaran::where('nama', $item['kategori'])->first();
            $data = $item;
            unset($data['kategori']);
            $data['kategori_id'] = $kategori->id;

            Pelanggaran::updateOrCreate(
                ['kode_pelanggaran' => $data['kode_pelanggaran']],
                $data
            );
        }
    }
}
