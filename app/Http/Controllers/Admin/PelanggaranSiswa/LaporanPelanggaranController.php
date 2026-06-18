<?php

namespace App\Http\Controllers\Admin\PelanggaranSiswa;

use App\Exports\LaporanBulananExport;
use App\Exports\LaporanKategoriExport;
use App\Exports\LaporanPelanggaranSiswaExport;
use App\Exports\LaporanPoinSiswaExport;
use App\Exports\LaporanStatusSiswaExport;
use App\Exports\LaporanJenisPelanggaranExport;
use App\Exports\RekapPelanggaranExport;
use App\Http\Controllers\Controller;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Services\LaporanFilterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPelanggaranController extends Controller
{
    public function __construct(protected LaporanFilterService $filterService)
    {
    }

    public function index(Request $request)
    {
        $tipe = $request->get('tipe', 'pelanggaran-siswa');
        $tipe = $this->filterService->validateTipe($tipe);

        [$start, $end] = $this->filterService->validateDateRange($request);

        if ($request->ajax()) {
            if ($tipe === 'pelanggaran-siswa') {
                $query = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori']);
                $this->filterService->applyCommonFilters($request, $query, $start, $end);
                
                $totalRecords = PelanggaranSiswa::count();
                $filteredRecords = $query->count();
                $totalPoin = $query->sum('poin');

                if ($request->filled('order.0.column')) {
                    $colIndex = $request->input('order.0.column');
                    $colDir = $request->input('order.0.dir', 'desc');
                    $columns = [
                        'id',
                        'tanggal_pelanggaran',
                        'siswa.nis',
                        'siswa.nama',
                        'siswa.kelas',
                        'siswa.jurusan',
                        'pelanggaran.nama_pelanggaran',
                        'kategori',
                        'pelanggaran.tingkat',
                        'poin',
                        'status_penanganan'
                    ];
                    $colName = $columns[$colIndex] ?? 'tanggal_pelanggaran';
                    
                    if (str_starts_with($colName, 'siswa.')) {
                        $relationCol = str_replace('siswa.', '', $colName);
                        $query->join('siswa', 'pelanggaran_siswa.siswa_id', '=', 'siswa.id')
                              ->select('pelanggaran_siswa.*')
                              ->orderBy('siswa.' . $relationCol, $colDir);
                    } elseif (str_starts_with($colName, 'pelanggaran.')) {
                        $relationCol = str_replace('pelanggaran.', '', $colName);
                        $query->join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
                              ->select('pelanggaran_siswa.*')
                              ->orderBy('pelanggaran.' . $relationCol, $colDir);
                    } elseif ($colName === 'kategori') {
                        $query->join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
                              ->join('kategori_pelanggaran', 'pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
                              ->select('pelanggaran_siswa.*')
                              ->orderBy('kategori_pelanggaran.nama', $colDir);
                    } else {
                        $query->orderBy('pelanggaran_siswa.' . $colName, $colDir);
                    }
                } else {
                    $query->orderBy('pelanggaran_siswa.tanggal_pelanggaran', 'desc')->orderBy('pelanggaran_siswa.id', 'desc');
                }

                $startLimit = $request->input('start', 0);
                $lengthLimit = $request->input('length', 10);
                $records = $query->skip($startLimit)->take($lengthLimit)->get();

                $data = [];
                foreach ($records as $index => $row) {
                    $data[] = [
                        'DT_RowIndex' => $startLimit + $index + 1,
                        'tanggal_pelanggaran' => $row->tanggal_pelanggaran ? $row->tanggal_pelanggaran->format('d/m/Y') : '-',
                        'nis' => $row->siswa->nis ?? '-',
                        'nama' => $row->siswa->nama ?? '-',
                        'kelas' => $row->siswa->kelas ?? '-',
                        'jurusan' => $row->siswa->jurusan ?? '-',
                        'pelanggaran' => $row->pelanggaran->nama_pelanggaran ?? '-',
                        'kategori' => $row->pelanggaran->kategori->nama ?? '-',
                        'tingkat' => $row->pelanggaran->tingkat ?? '-',
                        'poin' => $row->poin,
                        'status' => $row->status_penanganan ?? '-',
                    ];
                }

                return response()->json([
                    'draw' => intval($request->draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data,
                    'totalPoin' => $totalPoin
                ]);
            }
        }

        $data = $this->buildReportData($tipe, $request, $start, $end);

        return view('admin.pelanggaran-siswa.laporan.show', array_merge(
            $data,
            [
                'tipe'           => $tipe,
                'tipeOptions'    => LaporanFilterService::TIPE_LAPORAN,
                'startDate'      => $start,
                'endDate'        => $end,
                'filterOptions'  => $this->filterService->filterOptions(),
                'request'        => $request,
            ]
        ));
    }

    public function printPdf(Request $request, string $tipe)
    {
        $tipe = $this->filterService->validateTipe($tipe);
        [$start, $end] = $this->filterService->validateDateRange($request);

        $data = $this->buildReportData($tipe, $request, $start, $end);

        $viewMap = [
            'pelanggaran-siswa' => 'admin.pelanggaran-siswa.laporan.pdf.pelanggaran-siswa',
            'rekap-bulanan'     => 'admin.pelanggaran-siswa.laporan.pdf.rekap-bulanan',
            'rekap-kategori'    => 'admin.pelanggaran-siswa.laporan.pdf.rekap-kategori',
            'rekap-jenis'       => 'admin.pelanggaran-siswa.laporan.pdf.rekap-jenis',
            'rekap-kelas'       => 'admin.pelanggaran-siswa.laporan.pdf.rekap-kelas',
            'ranking-siswa'     => 'admin.pelanggaran-siswa.laporan.pdf.ranking-siswa',
            'status-penanganan' => 'admin.pelanggaran-siswa.laporan.pdf.status-penanganan',
        ];

        $pdf = Pdf::loadView($viewMap[$tipe], array_merge($data, [
            'tipe'         => $tipe,
            'tipeLabel'    => LaporanFilterService::TIPE_LAPORAN[$tipe],
            'startDate'    => $start,
            'endDate'      => $end,
            'tanggalMulai' => $start,
            'tanggalSelesai' => $end,
            'generatedAt'  => Carbon::now()->translatedFormat('d F Y H:i'),
        ]))->setPaper('a4', 'landscape');

        $filename = 'laporan-' . $tipe . '-' . Carbon::now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request, string $tipe)
    {
        $tipe = $this->filterService->validateTipe($tipe);
        [$start, $end] = $this->filterService->validateDateRange($request);

        $exportMap = [
            'pelanggaran-siswa' => LaporanPelanggaranSiswaExport::class,
            'rekap-bulanan'     => LaporanBulananExport::class,
            'rekap-kategori'    => LaporanKategoriExport::class,
            'rekap-jenis'       => LaporanJenisPelanggaranExport::class,
            'rekap-kelas'       => RekapPelanggaranExport::class,
            'ranking-siswa'     => LaporanPoinSiswaExport::class,
            'status-penanganan' => LaporanStatusSiswaExport::class,
        ];

        $class = $exportMap[$tipe];
        $filename = 'laporan-' . $tipe . '-' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $judul = LaporanFilterService::TIPE_LAPORAN[$tipe];

        // Check the constructor parameters of the class
        $ref = new \ReflectionClass($class);
        $ctor = $ref->getConstructor();
        $paramsCount = $ctor ? $ctor->getNumberOfParameters() : 0;

        if ($paramsCount === 3) {
            $data = $this->buildReportData($tipe, $request, $start, $end);
            return Excel::download(
                new $class(
                    $judul,
                    $data['headers'] ?? [],
                    $data['rows'] ?? []
                ),
                $filename
            );
        }

        return Excel::download(
            new $class(
                $this,
                $request,
                $start,
                $end,
                $judul
            ),
            $filename
        );
    }

    public function buildReportData(string $tipe, Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $method = 'build' . str_replace('-', '', ucwords($tipe, '-')) . 'Data';
        if (!method_exists($this, $method)) {
            abort(404, 'Builder untuk laporan ini tidak ditemukan.');
        }
        return $this->{$method}($request, $start, $end);
    }

    // Helper functions for Blade partials
    public function buildLaporanPelanggaranSiswa(Request $request)
    {
        [$start, $end] = $this->filterService->validateDateRange($request);
        $query = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori']);
        $this->filterService->applyCommonFilters($request, $query, $start, $end);
        return $query->orderBy('tanggal_pelanggaran', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function buildRekapBulanan($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $selectRaw = $isSqlite
            ? "strftime('%Y-%m', tanggal_pelanggaran) as periode, COUNT(*) as jumlah_pelanggaran, COUNT(DISTINCT siswa_id) as jumlah_siswa, SUM(poin) as total_poin"
            : "DATE_FORMAT(tanggal_pelanggaran, '%Y-%m') as periode, COUNT(*) as jumlah_pelanggaran, COUNT(DISTINCT siswa_id) as jumlah_siswa, SUM(poin) as total_poin";

        return $query
            ->selectRaw($selectRaw)
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();
    }

    public function buildRekapKategori($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        return $query->whereHas('pelanggaran')
            ->join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
            ->join('kategori_pelanggaran', 'pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->selectRaw('kategori_pelanggaran.nama as kategori, COUNT(pelanggaran_siswa.id) as jumlah_pelanggaran, COUNT(DISTINCT pelanggaran_siswa.siswa_id) as jumlah_siswa, SUM(pelanggaran_siswa.poin) as total_poin')
            ->groupBy('kategori_pelanggaran.id', 'kategori_pelanggaran.nama')
            ->orderBy('total_poin', 'desc')
            ->get();
    }

    public function buildRekapJenis($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        return $query->whereHas('pelanggaran')
            ->join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
            ->join('kategori_pelanggaran', 'pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->selectRaw('pelanggaran.kode_pelanggaran, pelanggaran.nama_pelanggaran, kategori_pelanggaran.nama as kategori, pelanggaran.tingkat, COUNT(pelanggaran_siswa.id) as jumlah_pelanggaran, COUNT(DISTINCT pelanggaran_siswa.siswa_id) as jumlah_siswa, SUM(pelanggaran_siswa.poin) as total_poin')
            ->groupBy('pelanggaran.id', 'pelanggaran.kode_pelanggaran', 'pelanggaran.nama_pelanggaran', 'pelanggaran.tingkat', 'kategori_pelanggaran.nama')
            ->orderBy('jumlah_pelanggaran', 'desc')
            ->get();
    }

    public function buildRekapKelas($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        return $query->whereHas('siswa')
            ->join('siswa', 'pelanggaran_siswa.siswa_id', '=', 'siswa.id')
            ->selectRaw('siswa.kelas, siswa.jurusan, COUNT(pelanggaran_siswa.id) as jumlah_pelanggaran, COUNT(DISTINCT siswa.id) as jumlah_siswa, SUM(pelanggaran_siswa.poin) as total_poin')
            ->groupBy('siswa.kelas', 'siswa.jurusan')
            ->orderBy('total_poin', 'desc')
            ->get();
    }

    public function buildRankingSiswa($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        $rows = $query
            ->join('siswa', 'pelanggaran_siswa.siswa_id', '=', 'siswa.id')
            ->selectRaw('siswa.nis, siswa.nama, siswa.kelas, siswa.jurusan, COUNT(pelanggaran_siswa.id) as jumlah_pelanggaran, SUM(pelanggaran_siswa.poin) as total_poin')
            ->groupBy('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.kelas', 'siswa.jurusan')
            ->orderBy('total_poin', 'desc')
            ->orderBy('jumlah_pelanggaran', 'desc')
            ->get();

        foreach ($rows as $row) {
            $row->status_pembinaan = $this->getStatusPembinaanLabel($row->total_poin);
        }

        return $rows;
    }

    private function getStatusPembinaanLabel(int $poin): string
    {
        return match (true) {
            $poin <= 25 => 'Aman',
            $poin <= 50 => 'Perhatian',
            $poin <= 75 => 'Pembinaan',
            $poin <= 100 => 'Panggilan Orang Tua',
            default => 'Rekomendasi Tindakan Khusus',
        };
    }

    public function buildRekapStatusPenanganan($startDate = null, $endDate = null)
    {
        $request = request();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $query = PelanggaranSiswa::query();
        $this->filterService->applyCommonFilters($request, $query, $start, $end);

        return $query
            ->selectRaw('status_penanganan, COUNT(*) as jumlah_pelanggaran, COUNT(DISTINCT siswa_id) as jumlah_siswa, SUM(poin) as total_poin')
            ->groupBy('status_penanganan')
            ->orderBy('status_penanganan')
            ->get();
    }

    // Builders called by buildReportData
    public function buildPelanggaranSiswaData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $riwayat = $this->buildLaporanPelanggaranSiswa($request);

        $headers = ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis Pelanggaran', 'Kategori', 'Tingkat', 'Poin', 'Status'];
        $rows = [];
        foreach ($riwayat as $row) {
            $rows[] = [
                $row->tanggal_pelanggaran ? $row->tanggal_pelanggaran->format('d/m/Y') : '-',
                $row->siswa->nis ?? '-',
                $row->siswa->nama ?? '-',
                $row->siswa->kelas ?? '-',
                $row->pelanggaran->nama_pelanggaran ?? '-',
                $row->pelanggaran->kategori->nama ?? '-',
                $row->pelanggaran->tingkat ?? '-',
                $row->poin,
                $row->status_penanganan ?? '-',
            ];
        }

        return [
            'riwayat' => $riwayat,
            'totalPoin' => $riwayat->sum('poin'),
            'total' => $riwayat->count(),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildRekapBulananData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRekapBulanan($start, $end);

        $headers = ['Periode (Bulan)', 'Jumlah Pelanggaran', 'Jumlah Siswa Terlibat', 'Total Poin'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                Carbon::parse($row->periode . '-01')->translatedFormat('F Y'),
                $row->jumlah_pelanggaran,
                $row->jumlah_siswa,
                $row->total_poin,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildRekapKategoriData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRekapKategori($start, $end);

        $headers = ['Kategori', 'Jumlah Pelanggaran', 'Jumlah Siswa Terlibat', 'Total Poin'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                $row->kategori,
                $row->jumlah_pelanggaran,
                $row->jumlah_siswa,
                $row->total_poin,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildRekapJenisData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRekapJenis($start, $end);

        $headers = ['Kode', 'Nama Pelanggaran', 'Kategori', 'Tingkat', 'Jumlah Pelanggaran', 'Jumlah Siswa Terlibat', 'Total Poin'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                $row->kode_pelanggaran,
                $row->nama_pelanggaran,
                $row->kategori,
                $row->tingkat,
                $row->jumlah_pelanggaran,
                $row->jumlah_siswa,
                $row->total_poin,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildRekapKelasData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRekapKelas($start, $end);

        $headers = ['Kelas', 'Jurusan', 'Jumlah Pelanggaran', 'Jumlah Siswa Terlibat', 'Total Poin'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                $row->kelas,
                $row->jurusan ?? '-',
                $row->jumlah_pelanggaran,
                $row->jumlah_siswa,
                $row->total_poin,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildRankingSiswaData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRankingSiswa($start, $end);

        $headers = ['NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Jumlah Pelanggaran', 'Total Poin', 'Status Pembinaan'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                $row->nis,
                $row->nama,
                $row->kelas,
                $row->jurusan ?? '-',
                $row->jumlah_pelanggaran,
                $row->total_poin,
                $row->status_pembinaan,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function buildStatusPenangananData(Request $request, ?Carbon $start, ?Carbon $end): array
    {
        $rekap = $this->buildRekapStatusPenanganan($start, $end);

        $headers = ['Status Penanganan', 'Jumlah Pelanggaran', 'Jumlah Siswa Terlibat', 'Total Poin'];
        $rows = [];
        foreach ($rekap as $row) {
            $rows[] = [
                $row->status_penanganan ?? 'Belum Diproses',
                $row->jumlah_pelanggaran,
                $row->jumlah_siswa,
                $row->total_poin,
            ];
        }

        return [
            'rekap' => $rekap,
            'total' => $rekap->sum('jumlah_pelanggaran'),
            'totalPoin' => $rekap->sum('total_poin'),
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
