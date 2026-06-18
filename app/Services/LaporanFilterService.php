<?php

namespace App\Services;

use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LaporanFilterService
{
    public const TIPE_LAPORAN = [
        'pelanggaran-siswa'   => 'Laporan Pelanggaran per Siswa',
        'rekap-bulanan'       => 'Rekap Bulanan Pelanggaran',
        'rekap-kategori'      => 'Rekap per Kategori Pelanggaran',
        'rekap-jenis'         => 'Rekap per Jenis Pelanggaran',
        'rekap-kelas'         => 'Rekap per Kelas / Jurusan',
        'ranking-siswa'       => 'Ranking Siswa Pelanggar',
        'status-penanganan'   => 'Status Penanganan Pelanggaran',
    ];

    public function validateTipe(string $tipe): string
    {
        if (!array_key_exists($tipe, self::TIPE_LAPORAN)) {
            abort(404, 'Jenis laporan tidak ditemukan.');
        }
        return $tipe;
    }

    public function validateDateRange(Request $request): array
    {
        $data = $request->validate([
            'tanggal_mulai'   => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $start = !empty($data['tanggal_mulai'])
            ? Carbon::parse($data['tanggal_mulai'])->startOfDay()
            : null;
        $end   = !empty($data['tanggal_selesai'])
            ? Carbon::parse($data['tanggal_selesai'])->endOfDay()
            : null;

        return [$start, $end];
    }

    public function applyDateRange($query, ?Carbon $start, ?Carbon $end, string $column = 'tanggal_pelanggaran'): void
    {
        if ($start) {
            $query->where($column, '>=', $start);
        }
        if ($end) {
            $query->where($column, '<=', $end);
        }
    }

    public function applyCommonFilters(Request $request, $query, ?Carbon $start = null, ?Carbon $end = null, string $column = 'tanggal_pelanggaran')
    {
        $this->applyDateRange($query, $start, $end, $column);

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        if ($request->filled('pelanggaran_id')) {
            $query->where('pelanggaran_id', $request->pelanggaran_id);
        }

        if ($request->filled('kategori_id')) {
            $query->whereHas('pelanggaran', function ($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('jurusan')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }

        if ($request->filled('status_penanganan')) {
            $query->where('status_penanganan', $request->status_penanganan);
        }

        if ($request->filled('tingkat')) {
            $query->whereHas('pelanggaran', function ($q) use ($request) {
                $q->where('tingkat', $request->tingkat);
            });
        }

        if ($request->filled('status_siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('status', $request->status_siswa);
            });
        }

        return $query;
    }

    public function filterOptions(): array
    {
        return [
            'siswa'            => Siswa::orderBy('nama')->pluck('nama', 'id')->toArray(),
            'kategori'         => KategoriPelanggaran::orderBy('nama')->pluck('nama', 'id')->toArray(),
            'pelanggaran'      => Pelanggaran::orderBy('nama_pelanggaran')->pluck('nama_pelanggaran', 'id')->toArray(),
            'kelasList'        => Siswa::whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'jurusanList'      => Siswa::whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
        ];
    }
}
