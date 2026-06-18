@extends('layouts.app')

@section('title', 'Catat Pelanggaran')
@section('page-title', 'Catat Pelanggaran Siswa')

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
    <li class="breadcrumb-item text-gray-900">Catat</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Catat Pelanggaran Siswa</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.riwayat.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Siswa</label>
                <div class="col-lg-8">
                    <select name="siswa_id" class="form-select form-select-solid form-select-lg @error('siswa_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih siswa">
                        <option value="">Pilih siswa</option>
                        @foreach($siswaList as $s)
                            <option value="{{ $s->id }}" {{ old('siswa_id', request('siswa_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama }} ({{ $s->nis }}) - {{ $s->kelas }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-muted fs-7 mt-2">Hanya siswa dengan status Aktif yang ditampilkan.</div>
                    @error('siswa_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jenis Pelanggaran</label>
                <div class="col-lg-8">
                    <select name="pelanggaran_id" class="form-select form-select-solid form-select-lg @error('pelanggaran_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih jenis pelanggaran">
                        <option value="">Pilih jenis pelanggaran</option>
                        @foreach($pelanggaranList as $p)
                            <option value="{{ $p->id }}" {{ old('pelanggaran_id') == $p->id ? 'selected' : '' }}>
                                [{{ $p->kode_pelanggaran }}] {{ $p->nama_pelanggaran }} - {{ $p->kategori->nama }} ({{ $p->poin }} poin)
                            </option>
                        @endforeach
                    </select>
                    <div class="text-muted fs-7 mt-2">Poin akan otomatis terisi dari master pelanggaran.</div>
                    @error('pelanggaran_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tanggal Pelanggaran</label>
                <div class="col-lg-8">
                    <input type="date" name="tanggal_pelanggaran" class="form-control form-control-lg form-control-solid @error('tanggal_pelanggaran') is-invalid @enderror"
                        value="{{ old('tanggal_pelanggaran', date('Y-m-d')) }}"/>
                    @error('tanggal_pelanggaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Catatan / Kronologi</label>
                <div class="col-lg-8">
                    <textarea name="catatan" class="form-control form-control-lg form-control-solid @error('catatan') is-invalid @enderror"
                        rows="3" placeholder="Ceritakan kronologi pelanggaran (opsional)">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Bukti (Foto/PDF)</label>
                <div class="col-lg-8">
                    <input type="file" name="bukti" class="form-control form-control-lg form-control-solid @error('bukti') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.pdf"/>
                    <div class="text-muted fs-7 mt-2">Format: JPG, JPEG, PNG, PDF. Maks 2MB. Opsional.</div>
                    @error('bukti')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status Penanganan</label>
                <div class="col-lg-8">
                    <select name="status_penanganan" class="form-select form-select-solid form-select-lg @error('status_penanganan') is-invalid @enderror">
                        <option value="Belum Diproses" {{ old('status_penanganan', 'Belum Diproses') === 'Belum Diproses' ? 'selected' : '' }}>Belum Diproses</option>
                        <option value="Diproses" {{ old('status_penanganan') === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Selesai" {{ old('status_penanganan') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status_penanganan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.riwayat.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
