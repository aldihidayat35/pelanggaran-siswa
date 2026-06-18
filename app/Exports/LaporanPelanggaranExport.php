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
use App\Models\Pelanggaran;

class LaporanPelanggaranExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        $query = Pelanggaran::with('kategori');

        if ($this->request->filled('kategori_id')) {
            $query->where('kategori_id', $this->request->kategori_id);
        }
        if ($this->request->filled('tingkat')) {
            $query->where('tingkat', $this->request->tingkat);
        }
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        return $query->orderBy('kode_pelanggaran', 'asc')->get();
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Nama Pelanggaran', 'Kategori', 'Tingkat', 'Poin', 'Status', 'Deskripsi'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kode_pelanggaran,
            $row->nama_pelanggaran,
            $row->kategori->nama ?? '-',
            $row->tingkat,
            $row->poin,
            $row->status,
            $row->deskripsi ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Data Pelanggaran';
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
