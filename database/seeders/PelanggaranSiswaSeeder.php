<?php

namespace Database\Seeders;

use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PelanggaranSiswaSeeder extends Seeder
{
    public function run(): void
    {
        $siswaList = Siswa::all();
        $pelanggaranList = Pelanggaran::all();

        if ($siswaList->isEmpty() || $pelanggaranList->isEmpty()) {
            return;
        }

        if (PelanggaranSiswa::exists()) {
            return;
        }

        $catatanSample = [
            'Terlambat masuk sekolah' => [
                'Terlambat karena kesiangan bangun tidur.',
                'Terlambat karena ban motor bocor di jalan.',
                'Terlambat karena macet di jalan raya utama.',
                'Terlambat karena mengantar adik terlebih dahulu.',
                'Terlambat tanpa alasan yang jelas.',
            ],
            'Tidak memakai atribut lengkap' => [
                'Tidak menggunakan dasi sekolah.',
                'Tidak memakai sabuk sekolah berlogo.',
                'Tidak menggunakan kaos kaki putih sesuai aturan.',
                'Topi tertinggal di rumah saat upacara.',
                'Memakai sepatu berwarna selain hitam polos.',
            ],
            'Membolos' => [
                'Meninggalkan kelas saat pelajaran matematika berlangsung.',
                'Lompat pagar belakang sekolah di jam istirahat kedua.',
                'Sembunyi di kantin luar sekolah saat jam pelajaran.',
                'Izin ke toilet tapi tidak kembali ke kelas.',
                'Terlihat nongkrong di warung sebelah sekolah saat jam KBM.',
            ],
            'Berkata kasar kepada guru/teman' => [
                'Berkata tidak sopan saat ditegur oleh guru piket.',
                'Mengejek teman sekelas dengan bahasa kasar.',
                'Membuat keributan verbal di dalam grup WhatsApp kelas.',
                'Berbicara tidak pantas kepada petugas perpustakaan.',
                'Mengumpat keras saat berpapasan dengan guru.',
            ],
            'Merokok di lingkungan sekolah' => [
                'Tertangkap merokok di area belakang kantin.',
                'Ditemukan membawa rokok dan korek api di dalam tas.',
                'Terdeteksi merokok di toilet lantai 2 sekolah.',
                'Tertangkap basah merokok bersama teman-teman di parkiran motor.',
                'Membawa rokok elektrik (vape) dan menghisapnya di kelas.',
            ],
            'Berkelahi' => [
                'Terlibat adu fisik dengan siswa sekolah lain di depan gerbang.',
                'Berkelahi di lapangan basket setelah pertandingan olahraga kelas.',
                'Mengeroyok teman sekelas karena salah paham.',
                'Terlibat perkelahian di lorong sekolah saat pergantian jam.',
            ],
            'Membawa benda berbahaya' => [
                'Ditemukan membawa cutter/pisau lipat di dalam tas saat razia.',
                'Membawa gir motor yang dimodifikasi di dalam bagasi motor.',
                'Membawa kembang api/mercon berbahaya ke sekolah.',
                'Membawa pemukul besi di dalam tas sekolah.',
            ],
        ];

        $statusPenangananOptions = ['Belum Diproses', 'Diproses', 'Selesai'];
        $officerOptions = ['Admin', 'Guru BK', 'Wali Kelas', 'Kesiswaan'];

        // Let's create 180 records spread over the last 12 months
        $totalRecords = 180;
        
        for ($i = 0; $i < $totalRecords; $i++) {
            // Pick random student and violation type
            $siswa = $siswaList->random();
            $pelanggaran = $pelanggaranList->random();

            // Select matching sample notes
            $name = $pelanggaran->nama_pelanggaran;
            $samples = $catatanSample[$name] ?? ['Melanggar tata tertib sekolah.'];
            $catatan = $samples[array_rand($samples)];

            // Select random date within last 12 months
            $daysAgo = rand(0, 365);
            $tanggal = Carbon::now()->subDays($daysAgo);

            // Assign status penanganan based on probability distribution (70% Selesai, 15% Diproses, 15% Belum)
            $randStatusVal = rand(1, 100);
            if ($randStatusVal <= 70) {
                $status = 'Selesai';
            } elseif ($randStatusVal <= 85) {
                $status = 'Diproses';
            } else {
                $status = 'Belum Diproses';
            }

            PelanggaranSiswa::create([
                'siswa_id'            => $siswa->id,
                'pelanggaran_id'      => $pelanggaran->id,
                'tanggal_pelanggaran' => $tanggal->format('Y-m-d'),
                'poin'                => $pelanggaran->poin, // snapshot
                'catatan'             => $catatan,
                'bukti'               => null,
                'dicatat_oleh'        => $officerOptions[array_rand($officerOptions)],
                'status_penanganan'   => $status,
            ]);
        }
    }
}
