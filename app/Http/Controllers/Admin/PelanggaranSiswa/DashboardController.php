<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::count();
        $totalJenisPelanggaran = Pelanggaran::count();
        $totalPelanggaran = PelanggaranSiswa::count();
        $totalSiswaPernahMelanggar = PelanggaranSiswa::distinct('siswa_id')->count('siswa_id');

        $topSiswa = Siswa::withSum('pelanggaranSiswa as total_poin', 'poin')
            ->orderByDesc('total_poin')
            ->limit(5)
            ->get();

        $riwayatTerbaru = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori'])
            ->latest('tanggal_pelanggaran')
            ->limit(10)
            ->get();

        $rankingSiswa = Siswa::withSum('pelanggaranSiswa as total_poin', 'poin')
            ->orderByDesc('total_poin')
            ->paginate(10);

        // Chart 1: Bulanan Trend
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFormat = $isSqlite ? "strftime('%Y-%m', tanggal_pelanggaran)" : "DATE_FORMAT(tanggal_pelanggaran, '%Y-%m')";

        $bulananTrend = PelanggaranSiswa::selectRaw("{$dateFormat} as bulan, COUNT(*) as jumlah")
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->limit(6)
            ->get();

        $chartBulanLabels = [];
        $chartBulanData = [];
        foreach ($bulananTrend as $item) {
            try {
                $carbon = Carbon::createFromFormat('Y-m', $item->bulan);
                $chartBulanLabels[] = $carbon->translatedFormat('F Y');
            } catch (\Exception $e) {
                $chartBulanLabels[] = $item->bulan;
            }
            $chartBulanData[] = $item->jumlah;
        }

        // Chart 2: Kategori
        $kategoriDist = PelanggaranSiswa::join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
            ->join('kategori_pelanggaran', 'pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->selectRaw('kategori_pelanggaran.nama as kategori, COUNT(*) as jumlah')
            ->groupBy('kategori_pelanggaran.id', 'kategori_pelanggaran.nama')
            ->get();

        $chartKategoriLabels = $kategoriDist->pluck('kategori')->toArray();
        $chartKategoriData = $kategoriDist->pluck('jumlah')->toArray();

        // Chart 3: Tingkat Pelanggaran (Severity)
        $tingkatDist = PelanggaranSiswa::join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
            ->selectRaw('pelanggaran.tingkat as tingkat, COUNT(*) as jumlah')
            ->groupBy('pelanggaran.tingkat')
            ->get();

        $chartTingkatLabels = $tingkatDist->pluck('tingkat')->toArray();
        $chartTingkatData = $tingkatDist->pluck('jumlah')->toArray();

        // Chart 4: Status Penanganan
        $statusDist = PelanggaranSiswa::selectRaw('status_penanganan, COUNT(*) as jumlah')
            ->groupBy('status_penanganan')
            ->get();

        $chartStatusLabels = $statusDist->pluck('status_penanganan')->toArray();
        $chartStatusData = $statusDist->pluck('jumlah')->toArray();

        return view('admin.pelanggaran-siswa.dashboard', compact(
            'totalSiswa',
            'totalJenisPelanggaran',
            'totalPelanggaran',
            'totalSiswaPernahMelanggar',
            'topSiswa',
            'riwayatTerbaru',
            'rankingSiswa',
            'chartBulanLabels',
            'chartBulanData',
            'chartKategoriLabels',
            'chartKategoriData',
            'chartTingkatLabels',
            'chartTingkatData',
            'chartStatusLabels',
            'chartStatusData'
        ));
    }
}
