@extends('layouts.app')

@section('title', 'Edit Pelanggaran')
@section('page-title', 'Edit Pelanggaran Siswa')

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
    <li class="breadcrumb-item text-gray-900">Edit</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Edit Pelanggaran</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.riwayat.update', $pelanggaranSiswa) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Siswa</label>
                <div class="col-lg-8">
                    <select name="siswa_id" class="form-select form-select-solid form-select-lg @error('siswa_id') is-invalid @enderror">
                        <option value="">Pilih siswa</option>
                        @foreach($siswaList as $s)
                            <option value="{{ $s->id }}" {{ old('siswa_id', $pelanggaranSiswa->siswa_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama }} ({{ $s->nis }}) - {{ $s->kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jenis Pelanggaran</label>
                <div class="col-lg-8">
                    <select name="pelanggaran_id" class="form-select form-select-solid form-select-lg @error('pelanggaran_id') is-invalid @enderror">
                        <option value="">Pilih jenis pelanggaran</option>
                        @foreach($pelanggaranList as $p)
                            <option value="{{ $p->id }}" {{ old('pelanggaran_id', $pelanggaranSiswa->pelanggaran_id) == $p->id ? 'selected' : '' }}>
                                [{{ $p->kode_pelanggaran }}] {{ $p->nama_pelanggaran }} - {{ $p->kategori->nama }} ({{ $p->poin }} poin)
                            </option>
                        @endforeach
                    </select>
                    <div class="text-muted fs-7 mt-2">Poin akan otomatis terisi dari master pelanggaran.</div>
                    @error('pelanggaran_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tanggal Pelanggaran</label>
                <div class="col-lg-8">
                    <input type="date" name="tanggal_pelanggaran" class="form-control form-control-lg form-control-solid @error('tanggal_pelanggaran') is-invalid @enderror"
                        value="{{ old('tanggal_pelanggaran', $pelanggaranSiswa->tanggal_pelanggaran->format('Y-m-d')) }}"/>
                    @error('tanggal_pelanggaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Catatan / Kronologi</label>
                <div class="col-lg-8">
                    <textarea name="catatan" class="form-control form-control-lg form-control-solid @error('catatan') is-invalid @enderror"
                        rows="3">{{ old('catatan', $pelanggaranSiswa->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Bukti (Foto/PDF)</label>
                <div class="col-lg-8">
                    @if($pelanggaranSiswa->bukti)
                        <div class="mb-3">
                            <span class="text-muted fs-7">File saat ini: </span>
                            <a href="{{ asset('storage/' . $pelanggaranSiswa->bukti) }}" target="_blank" class="text-primary">Lihat Bukti</a>
                        </div>
                    @endif
                    <input type="file" name="bukti" class="form-control form-control-lg form-control-solid @error('bukti') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.pdf"/>
                    <div class="text-muted fs-7 mt-2">Kosongkan jika tidak ingin mengubah bukti. Format: JPG, JPEG, PNG, PDF. Maks 2MB.</div>
                    @error('bukti')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status Penanganan</label>
                <div class="col-lg-8">
                    <select name="status_penanganan" class="form-select form-select-solid form-select-lg @error('status_penanganan') is-invalid @enderror">
                        <option value="Belum Diproses" {{ old('status_penanganan', $pelanggaranSiswa->status_penanganan) === 'Belum Diproses' ? 'selected' : '' }}>Belum Diproses</option>
                        <option value="Diproses" {{ old('status_penanganan', $pelanggaranSiswa->status_penanganan) === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Selesai" {{ old('status_penanganan', $pelanggaranSiswa->status_penanganan) === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status_penanganan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.riwayat.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
    </form>
</div>
@endsection
