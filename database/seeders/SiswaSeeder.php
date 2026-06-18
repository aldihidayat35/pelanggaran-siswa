<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            // Laki-laki
            ['nama' => 'Aditya Pratama', 'gender' => 'Laki-laki'],
            ['nama' => 'Ahmad Fauzi', 'gender' => 'Laki-laki'],
            ['nama' => 'Andi Wijaya', 'gender' => 'Laki-laki'],
            ['nama' => 'Aris Budiman', 'gender' => 'Laki-laki'],
            ['nama' => 'Bagus Setiawan', 'gender' => 'Laki-laki'],
            ['nama' => 'Budi Santoso', 'gender' => 'Laki-laki'],
            ['nama' => 'Candra Wijaya', 'gender' => 'Laki-laki'],
            ['nama' => 'Dedy Heryanto', 'gender' => 'Laki-laki'],
            ['nama' => 'Dimas Saputra', 'gender' => 'Laki-laki'],
            ['nama' => 'Dwi Cahyono', 'gender' => 'Laki-laki'],
            ['nama' => 'Eko Prasetyo', 'gender' => 'Laki-laki'],
            ['nama' => 'Fajar Nugroho', 'gender' => 'Laki-laki'],
            ['nama' => 'Galih Permana', 'gender' => 'Laki-laki'],
            ['nama' => 'Hendra Gunawan', 'gender' => 'Laki-laki'],
            ['nama' => 'Irfan Maulana', 'gender' => 'Laki-laki'],
            ['nama' => 'Joko Susilo', 'gender' => 'Laki-laki'],
            ['nama' => 'Kevin Sanjaya', 'gender' => 'Laki-laki'],
            ['nama' => 'Lukman Hakim', 'gender' => 'Laki-laki'],
            ['nama' => 'Muhammad Rifqi', 'gender' => 'Laki-laki'],
            ['nama' => 'Naufal Abdi', 'gender' => 'Laki-laki'],
            ['nama' => 'Rian Hidayat', 'gender' => 'Laki-laki'],
            ['nama' => 'Rizky Ramadhan', 'gender' => 'Laki-laki'],
            ['nama' => 'Satria Wibowo', 'gender' => 'Laki-laki'],
            ['nama' => 'Taufik Hidayat', 'gender' => 'Laki-laki'],
            ['nama' => 'Wahyu Hidayat', 'gender' => 'Laki-laki'],
            ['nama' => 'Yuda Pratama', 'gender' => 'Laki-laki'],
            ['nama' => 'Zacky Maulana', 'gender' => 'Laki-laki'],
            ['nama' => 'Rangga Wijaya', 'gender' => 'Laki-laki'],
            ['nama' => 'Gilang Ramadhan', 'gender' => 'Laki-laki'],
            ['nama' => 'Doni Setiawan', 'gender' => 'Laki-laki'],
            ['nama' => 'Erick Fernando', 'gender' => 'Laki-laki'],
            ['nama' => 'Ferry Hermawan', 'gender' => 'Laki-laki'],
            ['nama' => 'Heri Setiawan', 'gender' => 'Laki-laki'],
            ['nama' => 'Indra Wijaya', 'gender' => 'Laki-laki'],
            ['nama' => 'Tommy Kurniawan', 'gender' => 'Laki-laki'],

            // Perempuan
            ['nama' => 'Anisa Rahmawati', 'gender' => 'Perempuan'],
            ['nama' => 'Citra Lestari', 'gender' => 'Perempuan'],
            ['nama' => 'Dewi Sartika', 'gender' => 'Perempuan'],
            ['nama' => 'Dian Sastrowardoyo', 'gender' => 'Perempuan'],
            ['nama' => 'Elisa Putri', 'gender' => 'Perempuan'],
            ['nama' => 'Fitriani Lestari', 'gender' => 'Perempuan'],
            ['nama' => 'Gita Gutawa', 'gender' => 'Perempuan'],
            ['nama' => 'Indah Permatasari', 'gender' => 'Perempuan'],
            ['nama' => 'Kartika Sari', 'gender' => 'Perempuan'],
            ['nama' => 'Larasati Putri', 'gender' => 'Perempuan'],
            ['nama' => 'Mega Utami', 'gender' => 'Perempuan'],
            ['nama' => 'Nabila Syakieb', 'gender' => 'Perempuan'],
            ['nama' => 'Novia Bachmid', 'gender' => 'Perempuan'],
            ['nama' => 'Putri Ayu', 'gender' => 'Perempuan'],
            ['nama' => 'Ratih Purwasih', 'gender' => 'Perempuan'],
            ['nama' => 'Siti Aminah', 'gender' => 'Perempuan'],
            ['nama' => 'Tari Lestari', 'gender' => 'Perempuan'],
            ['nama' => 'Winda Kirana', 'gender' => 'Perempuan'],
            ['nama' => 'Yuni Shara', 'gender' => 'Perempuan'],
            ['nama' => 'Zahra Amelia', 'gender' => 'Perempuan'],
            ['nama' => 'Amalia Lestari', 'gender' => 'Perempuan'],
            ['nama' => 'Bella Citra', 'gender' => 'Perempuan'],
            ['nama' => 'Chelsea Olivia', 'gender' => 'Perempuan'],
            ['nama' => 'Dina Mariana', 'gender' => 'Perempuan'],
            ['nama' => 'Evelyn Wijaya', 'gender' => 'Perempuan'],
            ['nama' => 'Febby Rastanty', 'gender' => 'Perempuan'],
            ['nama' => 'Hana Saraswati', 'gender' => 'Perempuan'],
            ['nama' => 'Irma Suryani', 'gender' => 'Perempuan'],
            ['nama' => 'Jessica Mila', 'gender' => 'Perempuan'],
            ['nama' => 'Keke Monica', 'gender' => 'Perempuan'],
        ];

        $kelasOptions = ['X', 'XI', 'XII'];
        $jurusanOptions = ['RPL', 'TKJ', 'Multimedia'];

        $baseNis = 24001;
        $baseNisn = 10024001;

        foreach ($names as $i => $item) {
            $kelas = $kelasOptions[$i % count($kelasOptions)];
            $jurusan = $jurusanOptions[$i % count($jurusanOptions)];

            Siswa::create([
                'nis'             => (string) ($baseNis + $i),
                'nisn'            => (string) ($baseNisn + $i),
                'nama'            => $item['nama'],
                'jenis_kelamin'   => $item['gender'],
                'kelas'           => $kelas . ' ' . $jurusan . ' ' . (($i % 2) + 1),
                'jurusan'         => $jurusan,
                'no_hp_siswa'     => '08' . rand(111111111, 999999999),
                'nama_orang_tua'  => 'Orang Tua dari ' . $item['nama'],
                'no_hp_orang_tua' => '08' . rand(111111111, 999999999),
                'alamat'          => 'Jl. Pahlawan No. ' . ($i + 1) . ', Kota Bandung',
                'status'          => 'Aktif',
            ]);
        }
    }
}
