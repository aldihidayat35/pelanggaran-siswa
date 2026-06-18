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

class LaporanKategoriExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
{
    protected $request;
    protected $start;
    protected $end;
    protected $judul;

    public function __construct($controller, Request $request, $start, $end, $judul)
    {
        $this->request = $request;
        $this->start = $start;
        $this->end = $end;
        $this->judul = $judul;
    }

    public function collection()
    {
        $q = DB::table('pelanggaran_siswa as ps')
            ->join('pelanggaran as p', 'p.id', '=', 'ps.pelanggaran_id')
            ->join('kategori_pelanggaran as k', 'k.id', '=', 'p.kategori_id')
            ->select(
                'k.id',
                'k.nama as kategori',
                DB::raw('COUNT(*) as total_pelanggaran'),
                DB::raw('SUM(ps.poin) as total_poin'),
                DB::raw('COUNT(DISTINCT ps.siswa_id) as siswa_terlibat')
            )
            ->groupBy('k.id', 'k.nama');

        if ($this->start && $this->end) {
            $q->whereBetween('ps.tanggal_pelanggaran', [$this->start, $this->end]);
        }

        $rows = $q->orderBy('total_poin', 'desc')->get();

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Kategori', 'Total Pelanggaran', 'Siswa Terlibat', 'Total Poin'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kategori,
            $row->total_pelanggaran,
            $row->siswa_terlibat,
            $row->total_poin,
        ];
    }

    public function title(): string
    {
        return 'Laporan Per Kategori';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 4);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:E1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $periode = 'Periode: ' . ($this->start ? Carbon::parse($this->start)->format('d/m/Y') : '-') . ' s/d ' . ($this->end ? Carbon::parse($this->end)->format('d/m/Y') : '-');
                $event->sheet->setCellValue('A2', $periode);
                $event->sheet->mergeCells('A2:E2');

                $event->sheet->setCellValue('A3', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A3:E3');

                $headerRow = 5;
                $event->sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A5:E{$lastRow}")->applyFromArray([
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
