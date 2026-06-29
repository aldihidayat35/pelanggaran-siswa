@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('page-title', 'Edit Siswa')

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
    <li class="breadcrumb-item text-gray-900">Edit Siswa</li>
</ul>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h2>Edit Siswa: {{ $siswa->nama }}</h2>
        </div>
    </div>
    <form method="POST" action="{{ route('pelanggaran-siswa.siswa.update', $siswa) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Foto</label>
                <div class="col-lg-8">
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-125px h-125px"
                            style="background-image: url('{{ $siswa->foto ? asset('storage/' . $siswa->foto) : 'assets/media/avatars/blank.png' }}')"></div>
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
                        value="{{ old('nis', $siswa->nis) }}"/>
                    @error('nis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">NISN</label>
                <div class="col-lg-8">
                    <input type="text" name="nisn" class="form-control form-control-lg form-control-solid @error('nisn') is-invalid @enderror"
                        value="{{ old('nisn', $siswa->nisn) }}"/>
                    @error('nisn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Lengkap</label>
                <div class="col-lg-8">
                    <input type="text" name="nama" class="form-control form-control-lg form-control-solid @error('nama') is-invalid @enderror"
                        value="{{ old('nama', $siswa->nama) }}"/>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jenis Kelamin</label>
                <div class="col-lg-8">
                    <select name="jenis_kelamin" class="form-select form-select-solid form-select-lg @error('jenis_kelamin') is-invalid @enderror">
                        <option value="Laki-laki" {{ old('jenis_kelamin', $siswa->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $siswa->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
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
                        value="{{ old('kelas', $siswa->kelas) }}"/>
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Jurusan</label>
                <div class="col-lg-8">
                    <input type="text" name="jurusan" class="form-control form-control-lg form-control-solid @error('jurusan') is-invalid @enderror"
                        value="{{ old('jurusan', $siswa->jurusan) }}"/>
                    @error('jurusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">No HP Siswa</label>
                <div class="col-lg-8">
                    <input type="text" name="no_hp_siswa" class="form-control form-control-lg form-control-solid @error('no_hp_siswa') is-invalid @enderror"
                        value="{{ old('no_hp_siswa', $siswa->no_hp_siswa) }}"/>
                    @error('no_hp_siswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Nama Orang Tua</label>
                <div class="col-lg-8">
                    <input type="text" name="nama_orang_tua" class="form-control form-control-lg form-control-solid @error('nama_orang_tua') is-invalid @enderror"
                        value="{{ old('nama_orang_tua', $siswa->nama_orang_tua) }}"/>
                    @error('nama_orang_tua')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">No HP Orang Tua</label>
                <div class="col-lg-8">
                    <input type="text" name="no_hp_orang_tua" class="form-control form-control-lg form-control-solid @error('no_hp_orang_tua') is-invalid @enderror"
                        value="{{ old('no_hp_orang_tua', $siswa->no_hp_orang_tua) }}"/>
                    @error('no_hp_orang_tua')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Alamat</label>
                <div class="col-lg-8">
                    <textarea name="alamat" class="form-control form-control-lg form-control-solid @error('alamat') is-invalid @enderror"
                        rows="3">{{ old('alamat', $siswa->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status</label>
                <div class="col-lg-8">
                    <select name="status" class="form-select form-select-solid form-select-lg @error('status') is-invalid @enderror">
                        <option value="Aktif" {{ old('status', $siswa->status) === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', $siswa->status) === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="separator separator-dashed my-8"></div>

            <div class="row mb-3">
                <label class="col-lg-4 col-form-label fw-bold fs-6">
                    Pendaftaran Wajah
                    <span class="text-muted fs-7 fw-normal d-block">Opsional. Capture wajah via webcam untuk training FR.</span>
                </label>
                <div class="col-lg-8">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-semibold">Status Wajah</span>
                        @php
                            $initialCount = (int) ($dataset['image_count'] ?? 0);
                            $registered = $siswa->face_registered || $initialCount > 0;
                        @endphp
                        <span id="face_status_badge_edit" class="badge {{ $registered ? 'badge-light-success' : 'badge-light-danger' }}">
                            {{ $registered ? 'Wajah Terdaftar' : 'Belum Terdaftar' }}
                        </span>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="card border border-2 border-dashed border-primary h-100">
                                <div class="card-body p-4 text-center">
                                    <video id="webcam_edit" autoplay playsinline muted class="w-100 rounded mb-3" style="max-height:280px; background:#000;"></video>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" id="capture_btn_edit" class="btn btn-primary btn-sm">
                                            <i class="ki-duotone ki-camera fs-3"></i> Capture
                                        </button>
                                        <button type="button" id="stop_btn_edit" class="btn btn-light btn-sm">
                                            <i class="ki-duotone ki-cross fs-3"></i> Stop
                                        </button>
                                    </div>
                                    <div id="camera_error_edit" class="text-danger fs-7 mt-2" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body p-4 text-center">
                                    <canvas id="snapshot_canvas_edit" class="w-100 rounded mb-3 d-none" style="max-height:280px;"></canvas>
                                    <div id="snapshot_placeholder_edit" class="text-muted fs-7 py-5">
                                        <i class="ki-duotone ki-picture fs-5x text-muted mb-3 d-block"><span class="path1"></span><span class="path2"></span></i>
                                        Preview capture muncul di sini.
                                    </div>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" id="retake_btn_edit" class="btn btn-light btn-sm d-none">
                                            <i class="ki-duotone ki-arrows-loop fs-3"></i> Ulangi
                                        </button>
                                        <button type="button" id="use_btn_edit" class="btn btn-success btn-sm d-none">
                                            <i class="ki-duotone ki-check fs-3"></i> Pakai
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-7">Service FR:</span>
                            <span class="badge badge-light-{{ $health['success'] ?? false ? 'success' : 'danger' }} ms-1">
                                {{ ($health['success'] ?? false) ? 'Online' : 'Offline' }}
                            </span>
                            <span class="text-muted fs-7 ms-3">Dataset:</span>
                            <span id="dataset_count_edit" class="badge badge-light-info ms-1">{{ $initialCount }} Foto</span>
                        </div>
                        <a href="{{ route('pelanggaran-siswa.siswa.face-registration', $siswa) }}" class="btn btn-light-info btn-sm">
                            <i class="ki-duotone ki-face-id fs-3"></i> Halaman Penuh
                        </a>
                    </div>

                    <div class="form-text mt-2">Capture foto lalu klik "Pakai". Foto akan dikirim bersamaan saat Anda menyimpan form ini.</div>
                </div>
            </div>

            <input type="hidden" name="face_image" id="face_image_input_edit" value=""/>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('pelanggaran-siswa.siswa.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
    </form>
</div>
@endsection

@push('custom-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('webcam_edit');
    const canvas = document.getElementById('snapshot_canvas_edit');
    const placeholder = document.getElementById('snapshot_placeholder_edit');
    const captureBtn = document.getElementById('capture_btn_edit');
    const stopBtn = document.getElementById('stop_btn_edit');
    const retakeBtn = document.getElementById('retake_btn_edit');
    const useBtn = document.getElementById('use_btn_edit');
    const errBox = document.getElementById('camera_error_edit');
    const hiddenInput = document.getElementById('face_image_input_edit');

    if (!video || !captureBtn) return;

    let stream = null;

    async function startCamera() {
        try {
            errBox.style.display = 'none';
            stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 }, audio: false });
            video.srcObject = stream;
        } catch (e) {
            errBox.textContent = 'Tidak dapat mengakses webcam: ' + e.message;
            errBox.style.display = 'block';
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        video.srcObject = null;
    }

    captureBtn.addEventListener('click', function () {
        if (!stream) { startCamera(); return; }
        const w = video.videoWidth || 480;
        const h = video.videoHeight || 360;
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, w, h);
        canvas.classList.remove('d-none');
        placeholder.classList.add('d-none');
        retakeBtn.classList.remove('d-none');
        useBtn.classList.remove('d-none');
    });

    stopBtn.addEventListener('click', function () {
        stopCamera();
    });

    retakeBtn.addEventListener('click', function () {
        canvas.classList.add('d-none');
        placeholder.classList.remove('d-none');
        retakeBtn.classList.add('d-none');
        useBtn.classList.add('d-none');
        hiddenInput.value = '';
    });

    useBtn.addEventListener('click', function () {
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        hiddenInput.value = dataUrl;
        // Optimistically mark as registered for visual feedback after save
        const badge = document.getElementById('face_status_badge_edit');
        if (badge) {
            badge.classList.remove('badge-light-danger');
            badge.classList.add('badge-light-success');
            badge.textContent = 'Akan Tersimpan';
        }
        stopCamera();
    });

    // Auto-start camera when form mounts
    startCamera();

    // Stop camera on form submit to release the resource
    document.querySelector('form').addEventListener('submit', function () {
        stopCamera();
    });
});
</script>
@endpush
