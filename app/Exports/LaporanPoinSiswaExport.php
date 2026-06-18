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

class LaporanPoinSiswaExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        $query = DB::table('pelanggaran_siswa as ps')
            ->join('siswa as s', 's.id', '=', 'ps.siswa_id')
            ->leftJoin('pelanggaran as p', 'p.id', '=', 'ps.pelanggaran_id')
            ->leftJoin('kategori_pelanggaran as k', 'k.id', '=', 'p.kategori_id')
            ->select(
                's.id',
                's.nis',
                's.nama',
                's.kelas',
                's.jurusan',
                'k.nama as kategori',
                DB::raw('SUM(ps.poin) as total_poin'),
                DB::raw('COUNT(*) as total_pelanggaran')
            )
            ->groupBy('s.id', 's.nis', 's.nama', 's.kelas', 's.jurusan', 'k.nama');

        if ($this->request->filled('kategori_id')) {
            $query->where('k.id', $this->request->kategori_id);
        }

        $rows = $query->orderBy('total_poin', 'desc')->get();

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Kategori', 'Total Pelanggaran', 'Total Poin'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nis,
            $row->nama,
            $row->kelas,
            $row->jurusan,
            $row->kategori ?? '-',
            $row->total_pelanggaran,
            $row->total_poin,
        ];
    }

    public function title(): string
    {
        return 'Laporan Poin Siswa';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 3);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $event->sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A2:H2');

                $headerRow = 4;
                $event->sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
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
