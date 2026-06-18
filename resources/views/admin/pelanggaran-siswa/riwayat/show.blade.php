@extends('layouts.app')

@section('title', 'Detail Pelanggaran')
@section('page-title', 'Detail Pelanggaran')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.riwayat.index') }}" class="text-muted text-hover-primary">Riwayat Pelanggaran</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Detail</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Detail Pelanggaran</span>
            <span class="text-muted fs-7">Tanggal: {{ $pelanggaranSiswa->tanggal_pelanggaran->format('d M Y') }}</span>
        </h3>
        <div class="card-toolbar">
            <a href="{{ route('pelanggaran-siswa.riwayat.edit', $pelanggaranSiswa) }}" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-pencil fs-3"></i> Edit
            </a>
        </div>
    </div>
    <div class="card-body py-3">
        <table class="table table-row-bordered gy-5">
            <tr>
                <td class="fw-semibold text-muted w-200px">Siswa</td>
                <td>
                    <a href="{{ route('pelanggaran-siswa.siswa.show', $pelanggaranSiswa->siswa_id) }}" class="text-primary fw-bold">
                        {{ $pelanggaranSiswa->siswa->nama ?? '-' }}
                    </a>
                    <span class="text-muted fs-7">(NIS: {{ $pelanggaranSiswa->siswa->nis ?? '-' }})</span>
                </td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Jenis Pelanggaran</td>
                <td>{{ $pelanggaranSiswa->pelanggaran->nama_pelanggaran ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Kategori</td>
                <td>{{ $pelanggaranSiswa->pelanggaran->kategori->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Tingkat</td>
                <td>
                    @if($pelanggaranSiswa->pelanggaran)
                        <span class="badge {{ $pelanggaranSiswa->pelanggaran->tingkat_badge }}">{{ $pelanggaranSiswa->pelanggaran->tingkat }}</span>
                    @else - @endif
                </td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Poin</td>
                <td><span class="badge badge-light-warning fs-5 fw-bold">{{ $pelanggaranSiswa->poin }} Poin</span></td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Tanggal Pelanggaran</td>
                <td>{{ $pelanggaranSiswa->tanggal_pelanggaran->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Status Penanganan</td>
                <td><span class="badge {{ $pelanggaranSiswa->status_badge }} fs-6">{{ $pelanggaranSiswa->status_penanganan }}</span></td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Dicatat Oleh</td>
                <td>{{ $pelanggaranSiswa->dicatat_oleh ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Catatan / Kronologi</td>
                <td>{{ $pelanggaranSiswa->catatan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Bukti</td>
                <td>
                    @if($pelanggaranSiswa->bukti)
                        <a href="{{ asset('storage/' . $pelanggaranSiswa->bukti) }}" target="_blank" class="btn btn-sm btn-light-primary">
                            <i class="ki-duotone ki-file fs-3"></i> Lihat Bukti
                        </a>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="fw-semibold text-muted">Dicatat Pada</td>
                <td>{{ $pelanggaranSiswa->created_at->format('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-end py-6 px-9">
        <a href="{{ route('pelanggaran-siswa.riwayat.index') }}" class="btn btn-light">Kembali</a>
    </div>
</div>
@endsection
