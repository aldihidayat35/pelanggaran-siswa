@php
    $req = $request ?? request();
    $pdfUrl   = route('pelanggaran-siswa.laporan.pdf', array_merge(['tipe' => $tipe], $req->all()));
    $excelUrl = route('pelanggaran-siswa.laporan.excel', array_merge(['tipe' => $tipe], $req->all()));
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge badge-light-primary fs-7">Tipe: {{ ucwords(str_replace('-', ' ', $tipe)) }}</span>
        @if($req->filled('tanggal_mulai') || $req->filled('tanggal_selesai'))
            <span class="badge badge-light-info fs-7 ms-2">
                Periode: {{ $req->tanggal_mulai ?? '...' }} s/d {{ $req->tanggal_selesai ?? '...' }}
            </span>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-sm btn-light-danger">
            <i class="ki-duotone ki-document fs-2"></i> Cetak PDF
        </a>
        <a href="{{ $excelUrl }}" class="btn btn-sm btn-light-success">
            <i class="ki-duotone ki-file-down fs-2"></i> Export Excel
        </a>
    </div>
</div>
