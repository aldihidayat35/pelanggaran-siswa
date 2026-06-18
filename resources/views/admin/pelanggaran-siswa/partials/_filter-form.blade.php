@php
    $req = $request ?? request();
@endphp

<form method="GET" action="{{ $action }}" class="card card-flush mb-6">
    <div class="card-body">
        <div class="row g-5">
            @if(!empty($showTipe) && !empty($tipeOptions))
            <div class="col-md-3">
                <label class="form-label">Jenis Laporan</label>
                <select name="tipe" class="form-select form-select-solid">
                    @foreach($tipeOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($tipe ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showTanggalMulai))
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-solid"
                    value="{{ old('tanggal_mulai', $req->tanggal_mulai) }}">
            </div>
            @endif

            @if(!empty($showTanggalSelesai))
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-solid"
                    value="{{ old('tanggal_selesai', $req->tanggal_selesai) }}">
            </div>
            @endif

            @if(!empty($showSiswa) && !empty($filterOptions['siswa']))
            <div class="col-md-3">
                <label class="form-label">Siswa</label>
                <select name="siswa_id" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach($filterOptions['siswa'] as $id => $label)
                        <option value="{{ $id }}" @selected((string)$req->siswa_id === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showKategori) && !empty($filterOptions['kategori']))
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="kategori_id" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach($filterOptions['kategori'] as $id => $nama)
                        <option value="{{ $id }}" @selected((string)$req->kategori_id === (string)$id)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showPelanggaran) && !empty($filterOptions['pelanggaran']))
            <div class="col-md-3">
                <label class="form-label">Jenis Pelanggaran</label>
                <select name="pelanggaran_id" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach($filterOptions['pelanggaran'] as $id => $label)
                        <option value="{{ $id }}" @selected((string)$req->pelanggaran_id === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showKelas))
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <input type="text" name="kelas" class="form-control form-control-solid"
                    value="{{ old('kelas', $req->kelas) }}" placeholder="Contoh: XII RPL 1">
            </div>
            @endif

            @if(!empty($showJurusan))
            <div class="col-md-3">
                <label class="form-label">Jurusan</label>
                <input type="text" name="jurusan" class="form-control form-control-solid"
                    value="{{ old('jurusan', $req->jurusan) }}" placeholder="Contoh: RPL">
            </div>
            @endif

            @if(!empty($showTingkat))
            <div class="col-md-3">
                <label class="form-label">Tingkat</label>
                <select name="tingkat" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach(\App\Models\Pelanggaran::TINGKAT_OPTIONS as $key => $label)
                        <option value="{{ $key }}" @selected($req->tingkat === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showStatusPenanganan))
            <div class="col-md-3">
                <label class="form-label">Status Penanganan</label>
                <select name="status_penanganan" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach(\App\Models\PelanggaranSiswa::STATUS_PENANGANAN_OPTIONS as $key => $label)
                        <option value="{{ $key }}" @selected($req->status_penanganan === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(!empty($showStatusSiswa))
            <div class="col-md-3">
                <label class="form-label">Status Siswa</label>
                <select name="status_siswa" class="form-select form-select-solid">
                    <option value="">-- Semua --</option>
                    @foreach(\App\Models\Siswa::$statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($req->status_siswa === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="d-flex justify-content-end gap-2 mt-6">
            <a href="{{ $action }}" class="btn btn-light">Reset</a>
            <button type="submit" class="btn btn-primary">
                <i class="ki-duotone ki-magnifier fs-2"></i>
                Terapkan Filter
            </button>
        </div>
    </div>
</form>
