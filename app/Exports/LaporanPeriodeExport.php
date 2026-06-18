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

class LaporanPeriodeExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        $q = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori'])
            ->select('*');

        if ($this->start && $this->end) {
            $q->whereBetween('tanggal_pelanggaran', [$this->start, $this->end]);
        }

        $rows = $q->orderBy('tanggal_pelanggaran', 'asc')->get();

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis Pelanggaran', 'Kategori', 'Tingkat', 'Poin', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->tanggal_pelanggaran ? Carbon::parse($row->tanggal_pelanggaran)->format('d/m/Y') : '-',
            $row->siswa->nis ?? '-',
            $row->siswa->nama ?? '-',
            $row->siswa->kelas ?? '-',
            $row->pelanggaran->nama_pelanggaran ?? '-',
            $row->pelanggaran->kategori->nama ?? '-',
            $row->pelanggaran->tingkat ?? '-',
            $row->poin,
            $row->status_penanganan,
        ];
    }

    public function title(): string
    {
        return 'Laporan Periode';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 3);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:J1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $periode = 'Periode: ' . ($this->start ? Carbon::parse($this->start)->format('d/m/Y') : '-') . ' s/d ' . ($this->end ? Carbon::parse($this->end)->format('d/m/Y') : '-');
                $event->sheet->setCellValue('A2', $periode);
                $event->sheet->mergeCells('A2:J2');

                $event->sheet->setCellValue('A3', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A3:J3');

                $headerRow = 4;
                $event->sheet->getStyle("A{$headerRow}:J{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:J{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A4:J{$lastRow}")->applyFromArray([
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
