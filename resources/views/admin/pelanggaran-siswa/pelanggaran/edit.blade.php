@extends('layouts.app')

@section('title', 'Edit Pelanggaran')
@section('page-title', 'Edit Jenis Pelanggaran')

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
    <li class="breadcrumb-item text-gray-900">Edit</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Edit Pelanggaran: {{ $pelanggaran->nama_pelanggaran }}</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.pelanggaran.update', $pelanggaran) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Kode Pelanggaran</label>
                <div class="col-lg-8">
                    <input type="text" name="kode_pelanggaran" class="form-control form-control-lg form-control-solid @error('kode_pelanggaran') is-invalid @enderror"
                        value="{{ old('kode_pelanggaran', $pelanggaran->kode_pelanggaran) }}"/>
                    @error('kode_pelanggaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Pelanggaran</label>
                <div class="col-lg-8">
                    <input type="text" name="nama_pelanggaran" class="form-control form-control-lg form-control-solid @error('nama_pelanggaran') is-invalid @enderror"
                        value="{{ old('nama_pelanggaran', $pelanggaran->nama_pelanggaran) }}"/>
                    @error('nama_pelanggaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Kategori</label>
                <div class="col-lg-8">
                    <select name="kategori_id" class="form-select form-select-solid form-select-lg @error('kategori_id') is-invalid @enderror">
                        <option value="">Pilih kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id', $pelanggaran->kategori_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tingkat</label>
                <div class="col-lg-8">
                    <select name="tingkat" class="form-select form-select-solid form-select-lg @error('tingkat') is-invalid @enderror">
                        <option value="Ringan" {{ old('tingkat', $pelanggaran->tingkat) === 'Ringan' ? 'selected' : '' }}>Ringan</option>
                        <option value="Sedang" {{ old('tingkat', $pelanggaran->tingkat) === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="Berat" {{ old('tingkat', $pelanggaran->tingkat) === 'Berat' ? 'selected' : '' }}>Berat</option>
                    </select>
                    @error('tingkat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Poin</label>
                <div class="col-lg-8">
                    <input type="number" name="poin" min="1" class="form-control form-control-lg form-control-solid @error('poin') is-invalid @enderror"
                        value="{{ old('poin', $pelanggaran->poin) }}"/>
                    @error('poin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Deskripsi</label>
                <div class="col-lg-8">
                    <textarea name="deskripsi" class="form-control form-control-lg form-control-solid @error('deskripsi') is-invalid @enderror"
                        rows="3">{{ old('deskripsi', $pelanggaran->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status</label>
                <div class="col-lg-8">
                    <select name="status" class="form-select form-select-solid form-select-lg @error('status') is-invalid @enderror">
                        <option value="Aktif" {{ old('status', $pelanggaran->status) === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', $pelanggaran->status) === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.pelanggaran.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
    </form>
</div>
@endsection
