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
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class LaporanStatusSiswaExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        $query = Siswa::query();

        if ($this->request->filled('kelas')) {
            $query->where('kelas', $this->request->kelas);
        }
        if ($this->request->filled('jurusan')) {
            $query->where('jurusan', $this->request->jurusan);
        }

        $siswa = $query->orderBy('nama', 'asc')->get();

        $rows = $siswa->map(function ($s) {
            $totalPoin = (int) DB::table('pelanggaran_siswa')->where('siswa_id', $s->id)->sum('poin');
            $s->total_poin = $totalPoin;

            if ($totalPoin <= 25) {
                $s->status_pembinaan = 'Aman';
            } elseif ($totalPoin <= 50) {
                $s->status_pembinaan = 'Perhatian';
            } elseif ($totalPoin <= 75) {
                $s->status_pembinaan = 'Pembinaan';
            } elseif ($totalPoin <= 100) {
                $s->status_pembinaan = 'Panggilan Orang Tua';
            } else {
                $s->status_pembinaan = 'Rekomendasi Tindakan Khusus';
            }

            return $s;
        });

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama', 'Kelas', 'Jurusan', 'Status Siswa', 'Total Poin', 'Status Pembinaan'];
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
            $row->status,
            $row->total_poin,
            $row->status_pembinaan,
        ];
    }

    public function title(): string
    {
        return 'Status Siswa';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 2);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $event->sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A2:H2');

                $headerRow = 3;
                $event->sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:H{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
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
