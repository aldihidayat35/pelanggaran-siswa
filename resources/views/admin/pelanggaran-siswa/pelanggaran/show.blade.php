@extends('layouts.app')

@section('title', 'Detail Pelanggaran')
@section('page-title', 'Detail Jenis Pelanggaran')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.pelanggaran.index') }}" class="text-muted text-hover-primary">Jenis Pelanggaran</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">{{ $pelanggaran->nama_pelanggaran }}</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">{{ $pelanggaran->nama_pelanggaran }}</span>
            <span class="text-muted fs-7">Kode: {{ $pelanggaran->kode_pelanggaran }}</span>
        </h3>
        <div class="card-toolbar">
            <a href="{{ route('pelanggaran-siswa.pelanggaran.edit', $pelanggaran) }}" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-pencil fs-3"></i> Edit
            </a>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Kategori</label>
            <div class="col-lg-8">
                <span class="text-gray-800">{{ $pelanggaran->kategori->nama ?? '-' }}</span>
            </div>
        </div>
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Tingkat</label>
            <div class="col-lg-8">
                <span class="badge {{ $pelanggaran->tingkat_badge }} fs-6">{{ $pelanggaran->tingkat }}</span>
            </div>
        </div>
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Poin</label>
            <div class="col-lg-8">
                <span class="badge badge-light-primary fs-3 fw-bold">{{ $pelanggaran->poin }} Poin</span>
            </div>
        </div>
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Deskripsi</label>
            <div class="col-lg-8">
                <span class="text-gray-800">{{ $pelanggaran->deskripsi ?? '-' }}</span>
            </div>
        </div>
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
            <div class="col-lg-8">
                <span class="badge badge-light-{{ $pelanggaran->status === 'Aktif' ? 'success' : 'danger' }} fs-6">
                    {{ $pelanggaran->status }}
                </span>
            </div>
        </div>
        <div class="row mb-7">
            <label class="col-lg-4 col-form-label fw-semibold fs-6">Dibuat</label>
            <div class="col-lg-8">
                <span class="text-muted">{{ $pelanggaran->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end py-6 px-9">
        <a href="{{ route('pelanggaran-siswa.pelanggaran.index') }}" class="btn btn-light">Kembali</a>
    </div>
</div>
@endsection
