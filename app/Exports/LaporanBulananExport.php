<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PelanggaranSiswa;
use Illuminate\Support\Facades\DB;

class LaporanBulananExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
{
    protected $request;
    protected $judul;

    public function __construct($controller, Request $request, $start, $end, $judul)
    {
        $this->request = $request;
        $this->judul = $judul;
    }

    public function collection()
    {
        $year = $this->request->get('tahun', date('Y'));
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            $data = PelanggaranSiswa::select(
                    DB::raw("strftime('%Y', tanggal_pelanggaran) as tahun"),
                    DB::raw("cast(strftime('%m', tanggal_pelanggaran) as integer) as bulan"),
                    DB::raw('COUNT(*) as total_pelanggaran'),
                    DB::raw('SUM(poin) as total_poin'),
                    DB::raw('COUNT(DISTINCT siswa_id) as siswa_terlibat')
                )
                ->whereRaw("strftime('%Y', tanggal_pelanggaran) = ?", [$year])
                ->groupBy('tahun', 'bulan')
                ->orderBy('bulan')
                ->get();
        } else {
            $data = PelanggaranSiswa::select(
                    DB::raw('YEAR(tanggal_pelanggaran) as tahun'),
                    DB::raw('MONTH(tanggal_pelanggaran) as bulan'),
                    DB::raw('COUNT(*) as total_pelanggaran'),
                    DB::raw('SUM(poin) as total_poin'),
                    DB::raw('COUNT(DISTINCT siswa_id) as siswa_terlibat')
                )
                ->whereYear('tanggal_pelanggaran', $year)
                ->groupBy('tahun', 'bulan')
                ->orderBy('bulan')
                ->get();
        }

        return $data;
    }

    public function headings(): array
    {
        return ['No', 'Bulan', 'Tahun', 'Total Pelanggaran', 'Total Poin', 'Siswa Terlibat'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return [
            $no,
            $bulanNames[(int) $row->bulan] ?? $row->bulan,
            $row->tahun,
            $row->total_pelanggaran,
            $row->total_poin,
            $row->siswa_terlibat,
        ];
    }

    public function title(): string
    {
        return 'Laporan Bulanan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 3);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $year = $this->request->get('tahun', date('Y'));
                $event->sheet->setCellValue('A2', 'Tahun: ' . $year . ' | Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A2:F2');

                $headerRow = 4;
                $event->sheet->getStyle("A{$headerRow}:F{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:F{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A4:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
