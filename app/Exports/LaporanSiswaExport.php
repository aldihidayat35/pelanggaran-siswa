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

class LaporanSiswaExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }
        if ($this->request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $this->request->jenis_kelamin);
        }

        $siswa = $query->orderBy('nama', 'asc')->get();

        $rows = $siswa->map(function ($s) {
            $totalPoin = DB::table('pelanggaran_siswa')
                ->where('siswa_id', $s->id)
                ->sum('poin');

            $totalPelanggaran = DB::table('pelanggaran_siswa')
                ->where('siswa_id', $s->id)
                ->count();

            $s->total_poin = (int) $totalPoin;
            $s->total_pelanggaran = (int) $totalPelanggaran;

            return $s;
        });

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kelas', 'Jurusan', 'No HP Siswa', 'No HP Ortu', 'Status', 'Total Pelanggaran', 'Total Poin'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nis,
            $row->nisn ?? '-',
            $row->nama,
            $row->jenis_kelamin,
            $row->kelas,
            $row->jurusan,
            $row->no_hp_siswa ?? '-',
            $row->no_hp_orang_tua ?? '-',
            $row->status,
            $row->total_pelanggaran,
            $row->total_poin,
        ];
    }

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 2);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:L1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $event->sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A2:L2');

                $headerRow = 3;
                $event->sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:L{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A3:L{$lastRow}")->applyFromArray([
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
