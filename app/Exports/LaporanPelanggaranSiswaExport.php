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

class LaporanPelanggaranSiswaExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents
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
        $q = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori']);

        if ($this->start && $this->end) {
            $q->whereBetween('tanggal_pelanggaran', [$this->start, $this->end]);
        }

        if ($this->request->filled('kelas')) {
            $q->whereHas('siswa', function ($s) {
                $s->where('kelas', $this->request->kelas);
            });
        }

        if ($this->request->filled('jurusan')) {
            $q->whereHas('siswa', function ($s) {
                $s->where('jurusan', $this->request->jurusan);
            });
        }

        if ($this->request->filled('kategori_id')) {
            $q->whereHas('pelanggaran', function ($p) {
                $p->where('kategori_id', $this->request->kategori_id);
            });
        }

        if ($this->request->filled('tingkat')) {
            $q->whereHas('pelanggaran', function ($p) {
                $p->where('tingkat', $this->request->tingkat);
            });
        }

        if ($this->request->filled('status_penanganan')) {
            $q->where('status_penanganan', $this->request->status_penanganan);
        }

        return $q->orderBy('tanggal_pelanggaran', 'desc')->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Kode', 'Jenis Pelanggaran', 'Kategori', 'Tingkat', 'Poin', 'Status Penanganan', 'Dicatat Oleh', 'Catatan'];
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
            $row->siswa->jurusan ?? '-',
            $row->pelanggaran->kode_pelanggaran ?? '-',
            $row->pelanggaran->nama_pelanggaran ?? '-',
            $row->pelanggaran->kategori->nama ?? '-',
            $row->pelanggaran->tingkat ?? '-',
            $row->poin,
            $row->status_penanganan,
            $row->dicatat_oleh ?? '-',
            $row->catatan ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Laporan Pelanggaran';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->insertNewRowBefore(1, 4);

                $event->sheet->setCellValue('A1', $this->judul);
                $event->sheet->mergeCells('A1:N1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $periode = 'Periode: ' . ($this->start ? Carbon::parse($this->start)->format('d/m/Y') : '-') . ' s/d ' . ($this->end ? Carbon::parse($this->end)->format('d/m/Y') : '-');
                $event->sheet->setCellValue('A2', $periode);
                $event->sheet->mergeCells('A2:N2');

                $event->sheet->setCellValue('A3', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells('A3:N3');

                $headerRow = 5;
                $event->sheet->getStyle("A{$headerRow}:N{$headerRow}")->getFont()->setBold(true);
                $event->sheet->getStyle("A{$headerRow}:N{$headerRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A5:N{$lastRow}")->applyFromArray([
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
