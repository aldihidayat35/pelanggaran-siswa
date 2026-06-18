@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori Pelanggaran')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.kategori.index') }}" class="text-muted text-hover-primary">Kategori Pelanggaran</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Edit</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Edit Kategori: {{ $kategori->nama }}</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.kategori.update', $kategori) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Kategori</label>
                <div class="col-lg-8">
                    <input type="text" name="nama" class="form-control form-control-lg form-control-solid @error('nama') is-invalid @enderror"
                        value="{{ old('nama', $kategori->nama) }}"/>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Deskripsi</label>
                <div class="col-lg-8">
                    <textarea name="deskripsi" class="form-control form-control-lg form-control-solid @error('deskripsi') is-invalid @enderror"
                        rows="3">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status</label>
                <div class="col-lg-8">
                    <select name="status" class="form-select form-select-solid form-select-lg @error('status') is-invalid @enderror">
                        <option value="Aktif" {{ old('status', $kategori->status) === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', $kategori->status) === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.kategori.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
    </form>
</div>
@endsection
