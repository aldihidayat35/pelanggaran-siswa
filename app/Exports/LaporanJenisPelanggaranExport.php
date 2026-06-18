<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanJenisPelanggaranExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected string $judul,
        protected array $headers,
        protected $rows,
    ) {}

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('admin.pelanggaran-siswa.laporan.exports.jenis-pelanggaran', [
            'judul' => $this->judul,
            'headers' => $this->headers,
            'rows' => $this->rows,
        ]);
    }

    public function title(): string
    {
        return 'Laporan Jenis Pelanggaran';
    }
}
