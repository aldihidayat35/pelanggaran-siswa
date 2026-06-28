@extends('layouts.app')

@section('title', 'Detail Siswa')
@section('page-title', 'Detail Siswa')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.siswa.index') }}" class="text-muted text-hover-primary">Data Siswa</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">{{ $siswa->nama }}</li>
</ul>
@endsection

@section('content')
@php $status = $siswa->status_pembinaan; @endphp
<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <div class="card mb-5 mb-xl-8">
            <div class="card-body pt-15 pb-0">
                <div class="d-flex flex-center flex-column mb-5">
                    <div class="symbol symbol-150px symbol-circle mb-5">
                        @if($siswa->foto)
                            <img src="{{ asset('storage/' . $siswa->foto) }}" alt="{{ $siswa->nama }}"/>
                        @else
                            <div class="symbol-label fs-1 bg-light-primary text-primary">
                                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $siswa->nama }}</a>
                    <div class="fs-6 fw-semibold text-muted mb-2">NIS: {{ $siswa->nis }} @if($siswa->nisn) / {{ $siswa->nisn }} @endif</div>
                    <span class="badge badge-light-{{ $siswa->status === 'Aktif' ? 'success' : 'danger' }} mb-5">{{ $siswa->status }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Biodata Siswa</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('pelanggaran-siswa.siswa.face-registration', $siswa) }}" class="btn btn-sm btn-light-success me-2">
                        <i class="ki-duotone ki-scan-barcode fs-3"></i> Daftar Wajah
                    </a>
                    <a href="{{ route('pelanggaran-siswa.siswa.edit', $siswa) }}" class="btn btn-sm btn-light-primary">
                        <i class="ki-duotone ki-pencil fs-3"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body py-3">
                <table class="table table-row-bordered gy-5">
                    <tr>
                        <td class="fw-semibold text-muted w-200px">Nama Lengkap</td>
                        <td>{{ $siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Jenis Kelamin</td>
                        <td>{{ $siswa->jenis_kelamin }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Kelas</td>
                        <td>{{ $siswa->kelas }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Jurusan</td>
                        <td>{{ $siswa->jurusan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">No HP Siswa</td>
                        <td>{{ $siswa->no_hp_siswa ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Nama Orang Tua</td>
                        <td>{{ $siswa->nama_orang_tua ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">No HP Orang Tua</td>
                        <td>{{ $siswa->no_hp_orang_tua ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Alamat</td>
                        <td>{{ $siswa->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Status Pembinaan</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center mb-5">
                    <div class="fs-2 fw-bold text-gray-900 me-5">{{ $siswa->total_poin }} Poin</div>
                    <span class="badge {{ $status['badge'] }} fs-5 fw-bold">{{ $status['label'] }}</span>
                </div>
                <div class="text-muted fs-7">
                    Batasan poin:
                    <ul class="mt-2">
                        <li>0 - 25: Aman</li>
                        <li>26 - 50: Perhatian</li>
                        <li>51 - 75: Pembinaan</li>
                        <li>76 - 100: Panggilan Orang Tua</li>
                        <li>&gt; 100: Rekomendasi Tindakan Khusus</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-12">
        <div class="card card-xl-stretch mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Riwayat Pelanggaran</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('pelanggaran-siswa.riwayat.create') }}?siswa_id={{ $siswa->id }}" class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-plus fs-3"></i> Catat Pelanggaran
                    </a>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-100px">Tanggal</th>
                                <th class="min-w-200px">Pelanggaran</th>
                                <th class="min-w-150px">Kategori</th>
                                <th class="min-w-80px">Poin</th>
                                <th class="min-w-150px">Status</th>
                                <th class="min-w-200px">Catatan</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                            <tr>
                                <td><span class="text-muted fw-semibold d-block fs-7">{{ $r->tanggal_pelanggaran->format('d M Y') }}</span></td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">{{ $r->pelanggaran->nama_pelanggaran ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fs-7">{{ $r->pelanggaran->kategori->nama ?? '-' }}</span>
                                </td>
                                <td><span class="badge badge-light-warning fs-7 fw-bold">{{ $r->poin }}</span></td>
                                <td><span class="badge {{ $r->status_badge }} fs-7 fw-semibold">{{ $r->status_penanganan }}</span></td>
                                <td><span class="text-muted fs-7">{{ $r->catatan ?? '-' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('pelanggaran-siswa.riwayat.show', $r) }}" class="btn btn-sm btn-light-primary">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-10">Belum ada riwayat pelanggaran untuk siswa ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
