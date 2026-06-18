@extends('layouts.app')

@section('title', 'Tambah Siswa')
@section('page-title', 'Tambah Siswa')

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
    <li class="breadcrumb-item text-gray-900">Tambah Siswa</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Tambah Siswa Baru</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.siswa.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Foto</label>
                <div class="col-lg-8">
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url('assets/media/avatars/blank.png')"></div>
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Ganti foto">
                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                            <input type="file" name="foto" accept=".png, .jpg, .jpeg"/>
                            <input type="hidden" name="foto_remove"/>
                        </label>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Batalkan">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Hapus foto">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                    </div>
                    @error('foto')
                        <div class="text-danger mt-2 fs-7">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">NIS</label>
                <div class="col-lg-8">
                    <input type="text" name="nis" class="form-control form-control-lg form-control-solid @error('nis') is-invalid @enderror"
                        placeholder="Nomor Induk Siswa" value="{{ old('nis') }}"/>
                    @error('nis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">NISN</label>
                <div class="col-lg-8">
                    <input type="text" name="nisn" class="form-control form-control-lg form-control-solid @error('nisn') is-invalid @enderror"
                        placeholder="Nomor Induk Siswa Nasional (opsional)" value="{{ old('nisn') }}"/>
                    @error('nisn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Lengkap</label>
                <div class="col-lg-8">
                    <input type="text" name="nama" class="form-control form-control-lg form-control-solid @error('nama') is-invalid @enderror"
                        placeholder="Nama lengkap siswa" value="{{ old('nama') }}"/>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jenis Kelamin</label>
                <div class="col-lg-8">
                    <select name="jenis_kelamin" class="form-select form-select-solid form-select-lg @error('jenis_kelamin') is-invalid @enderror">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Kelas</label>
                <div class="col-lg-8">
                    <input type="text" name="kelas" class="form-control form-control-lg form-control-solid @error('kelas') is-invalid @enderror"
                        placeholder="Contoh: XII IPA 1" value="{{ old('kelas') }}"/>
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Jurusan</label>
                <div class="col-lg-8">
                    <input type="text" name="jurusan" class="form-control form-control-lg form-control-solid @error('jurusan') is-invalid @enderror"
                        placeholder="Jurusan (opsional)" value="{{ old('jurusan') }}"/>
                    @error('jurusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">No HP Siswa</label>
                <div class="col-lg-8">
                    <input type="text" name="no_hp_siswa" class="form-control form-control-lg form-control-solid @error('no_hp_siswa') is-invalid @enderror"
                        placeholder="08xxxxxxxxxx" value="{{ old('no_hp_siswa') }}"/>
                    @error('no_hp_siswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Nama Orang Tua</label>
                <div class="col-lg-8">
                    <input type="text" name="nama_orang_tua" class="form-control form-control-lg form-control-solid @error('nama_orang_tua') is-invalid @enderror"
                        placeholder="Nama orang tua / wali" value="{{ old('nama_orang_tua') }}"/>
                    @error('nama_orang_tua')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">No HP Orang Tua</label>
                <div class="col-lg-8">
                    <input type="text" name="no_hp_orang_tua" class="form-control form-control-lg form-control-solid @error('no_hp_orang_tua') is-invalid @enderror"
                        placeholder="08xxxxxxxxxx" value="{{ old('no_hp_orang_tua') }}"/>
                    @error('no_hp_orang_tua')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Alamat</label>
                <div class="col-lg-8">
                    <textarea name="alamat" class="form-control form-control-lg form-control-solid @error('alamat') is-invalid @enderror"
                        rows="3" placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status</label>
                <div class="col-lg-8">
                    <select name="status" class="form-select form-select-solid form-select-lg @error('status') is-invalid @enderror">
                        <option value="Aktif" {{ old('status', 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.siswa.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
