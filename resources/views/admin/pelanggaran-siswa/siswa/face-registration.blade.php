@extends('layouts.app')

@section('title', 'Daftar Wajah Siswa')
@section('page-title', 'Daftar Wajah Siswa')

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
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('pelanggaran-siswa.siswa.show', $siswa) }}" class="text-muted text-hover-primary">{{ $siswa->nama }}</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Daftar Wajah</li>
</ul>
@endsection

@section('content')
@php
    $initialImageCount = (int) ($dataset['image_count'] ?? 0);
    $targetCaptures = 8;
    $serviceOnline = (bool) (($health['status'] ?? null) === 'active');
@endphp

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <div class="card mb-5 mb-xl-8">
            <div class="card-body pt-10">
                <div class="d-flex flex-center flex-column mb-8">
                    <div class="symbol symbol-125px symbol-circle mb-5">
                        @if($siswa->foto)
                            <img src="{{ asset('storage/' . $siswa->foto) }}" alt="{{ $siswa->nama }}"/>
                        @else
                            <div class="symbol-label fs-1 bg-light-primary text-primary">
                                {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="fs-3 text-gray-800 fw-bold mb-1">{{ $siswa->nama }}</div>
                    <div class="fs-6 fw-semibold text-muted mb-2">NIS: {{ $siswa->nis }}</div>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <span class="badge badge-light-{{ $siswa->status === 'Aktif' ? 'success' : 'danger' }}">{{ $siswa->status }}</span>
                        <span class="badge badge-light-primary">{{ $siswa->kelas }}</span>
                    </div>
                </div>

                <div class="separator my-6"></div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted fw-semibold">Dataset</span>
                    <span class="badge badge-light-info" id="dataset_count">{{ $initialImageCount }} Foto</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted fw-semibold">Status Wajah</span>
                    <span class="badge {{ $initialImageCount > 0 ? 'badge-light-success' : 'badge-light-danger' }}" id="face_status_badge">
                        {{ $initialImageCount > 0 ? 'Terdaftar' : 'Belum Terdaftar' }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted fw-semibold">Service FR</span>
                    <span class="badge badge-light-{{ $serviceOnline ? 'success' : 'danger' }}" id="service_status">
                        {{ $serviceOnline ? 'Online' : 'Offline' }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted fw-semibold">Pipeline</span>
                    <span class="badge badge-light-secondary">{{ $health['pipeline_version'] ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Capture Wajah</span>
                    <span class="text-muted fw-semibold fs-7">Target {{ $targetCaptures }} foto valid untuk training LBPH</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('pelanggaran-siswa.siswa.show', $siswa) }}" class="btn btn-sm btn-light">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative w-100 bg-dark rounded-4 overflow-hidden mb-6 d-flex align-items-center justify-content-center" style="aspect-ratio: 4/3; min-height: 260px;">
                    <video id="video_stream" autoplay playsinline class="w-100 h-100" style="object-fit: cover; display: none;"></video>
                    <div id="camera_placeholder" class="text-center p-8">
                        <div class="symbol symbol-70px symbol-circle mb-4 bg-light-dark d-inline-flex align-items-center justify-content-center">
                            <i class="ki-duotone ki-camera text-gray-400 fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <div class="text-gray-400 fw-bold fs-5 mb-1">Kamera Nonaktif</div>
                        <div class="text-muted fs-7">Aktifkan kamera untuk mulai capture dataset wajah.</div>
                    </div>
                    <div id="capture_frame" class="position-absolute start-50 top-50 translate-middle rounded-4 border border-success border-3 d-none" style="width: 58%; height: 74%; box-shadow: 0 0 0 9999px rgba(0,0,0,0.35); pointer-events: none;"></div>
                </div>

                <canvas id="frame_canvas" class="d-none" width="640" height="480"></canvas>

                <div class="mb-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-gray-700" id="capture_status">Siap</span>
                        <span class="fw-bold text-gray-900"><span id="capture_count">0</span>/{{ $targetCaptures }}</span>
                    </div>
                    <div class="progress bg-light-success" style="height: 12px;">
                        <div id="capture_progress" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="alert bg-light-secondary border border-secondary d-flex align-items-center p-4 mb-6" id="status_alert">
                    <i class="ki-duotone ki-information-3 fs-2hx text-gray-500 me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div>
                        <div class="fw-bold text-gray-800" id="status_title">Sistem Siap</div>
                        <div class="text-gray-600 fs-7" id="status_desc">Dataset saat ini berisi {{ $initialImageCount }} foto.</div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-end">
                    <button type="button" class="btn btn-light" id="btn_stop_camera" disabled>
                        <i class="ki-duotone ki-cross-circle fs-3"></i> Stop Kamera
                    </button>
                    <button type="button" class="btn btn-primary" id="btn_start_camera" {{ !$serviceOnline || $siswa->status !== 'Aktif' ? 'disabled' : '' }}>
                        <i class="ki-duotone ki-camera fs-3"></i> Aktifkan Kamera
                    </button>
                    <button type="button" class="btn btn-success" id="btn_start_capture" disabled>
                        <i class="ki-duotone ki-scan-barcode fs-3"></i> Mulai Capture
                    </button>
                    <button type="button" class="btn btn-warning" id="btn_train_model" {{ $initialImageCount <= 0 ? 'disabled' : '' }}>
                        <i class="ki-duotone ki-arrows-loop fs-3"></i> Latih Ulang Model
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetCaptures = {{ $targetCaptures }};
    const video = document.getElementById('video_stream');
    const canvas = document.getElementById('frame_canvas');
    const ctx = canvas.getContext('2d');
    const cameraPlaceholder = document.getElementById('camera_placeholder');
    const captureFrame = document.getElementById('capture_frame');
    const btnStartCamera = document.getElementById('btn_start_camera');
    const btnStopCamera = document.getElementById('btn_stop_camera');
    const btnStartCapture = document.getElementById('btn_start_capture');
    const btnTrainModel = document.getElementById('btn_train_model');
    const captureCount = document.getElementById('capture_count');
    const captureProgress = document.getElementById('capture_progress');
    const captureStatus = document.getElementById('capture_status');
    const datasetCount = document.getElementById('dataset_count');
    const statusAlert = document.getElementById('status_alert');
    const statusTitle = document.getElementById('status_title');
    const statusDesc = document.getElementById('status_desc');

    let stream = null;
    let captured = 0;
    let captureInProgress = false;
    let captureTimer = null;

    function setStatus(title, desc, type) {
        statusTitle.innerText = title;
        statusDesc.innerText = desc;
        statusAlert.className = 'alert d-flex align-items-center p-4 mb-6 border';
        const icon = statusAlert.querySelector('i');
        if (type === 'success') {
            statusAlert.classList.add('bg-light-success', 'border-success');
            icon.className = 'ki-duotone ki-shield-tick fs-2hx text-success me-4';
        } else if (type === 'danger') {
            statusAlert.classList.add('bg-light-danger', 'border-danger');
            icon.className = 'ki-duotone ki-shield-cross fs-2hx text-danger me-4';
        } else if (type === 'warning') {
            statusAlert.classList.add('bg-light-warning', 'border-warning');
            icon.className = 'ki-duotone ki-information-3 fs-2hx text-warning me-4';
        } else {
            statusAlert.classList.add('bg-light-secondary', 'border-secondary');
            icon.className = 'ki-duotone ki-information-3 fs-2hx text-gray-500 me-4';
        }
        icon.innerHTML = '<span class="path1"></span><span class="path2"></span><span class="path3"></span>';
    }

    function updateProgress() {
        captureCount.innerText = captured;
        const pct = Math.round((captured / targetCaptures) * 100);
        captureProgress.style.width = pct + '%';
        captureProgress.setAttribute('aria-valuenow', pct);
        captureStatus.innerText = captured >= targetCaptures ? 'Capture selesai' : 'Capture berjalan';
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            video.style.display = 'block';
            cameraPlaceholder.classList.add('d-none');
            captureFrame.classList.remove('d-none');
            btnStartCamera.disabled = true;
            btnStopCamera.disabled = false;
            btnStartCapture.disabled = false;
            setStatus('Kamera Aktif', 'Wajah siswa siap dicapture.', 'success');
        } catch (error) {
            setStatus('Kamera Gagal', 'Browser tidak dapat mengakses kamera.', 'danger');
        }
    }

    function stopCamera() {
        if (captureTimer) {
            clearTimeout(captureTimer);
            captureTimer = null;
        }
        captureInProgress = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        video.style.display = 'none';
        cameraPlaceholder.classList.remove('d-none');
        captureFrame.classList.add('d-none');
        btnStartCamera.disabled = false;
        btnStopCamera.disabled = true;
        btnStartCapture.disabled = true;
    }

    function captureFrameToBase64() {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        return canvas.toDataURL('image/jpeg', 0.9);
    }

    function scheduleNextCapture(delay = 900) {
        if (!captureInProgress || captured >= targetCaptures) return;
        captureTimer = setTimeout(captureOnce, delay);
    }

    function captureOnce() {
        if (!stream || !captureInProgress || captured >= targetCaptures) return;

        axios.post("{{ route('pelanggaran-siswa.siswa.face-registration.capture', $siswa) }}", {
            image: captureFrameToBase64()
        })
        .then(response => {
            const res = response.data;
            if (res.success) {
                captured += 1;
                updateProgress();
                datasetCount.innerText = (res.image_count || captured) + ' Foto';
                btnTrainModel.disabled = false;
                setStatus('Foto Tersimpan', res.message || 'Foto wajah berhasil disimpan.', 'success');
            } else {
                setStatus('Capture Ditolak', res.message || 'Wajah belum valid untuk disimpan.', 'warning');
            }

            if (captured >= targetCaptures) {
                captureInProgress = false;
                btnStartCapture.disabled = false;
                btnStartCapture.innerText = 'Capture Ulang';
                setStatus('Capture Selesai', targetCaptures + ' foto valid berhasil disimpan.', 'success');
                return;
            }
            scheduleNextCapture();
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Gagal menyimpan foto wajah.';
            setStatus('Capture Gagal', msg, 'warning');
            scheduleNextCapture(1200);
        });
    }

    btnStartCamera.addEventListener('click', startCamera);
    btnStopCamera.addEventListener('click', stopCamera);

    btnStartCapture.addEventListener('click', function () {
        if (!stream || captureInProgress) return;
        captured = 0;
        captureInProgress = true;
        btnStartCapture.disabled = true;
        updateProgress();
        setStatus('Capture Berjalan', 'Sistem menyimpan foto wajah valid ke dataset.', 'success');
        captureOnce();
    });

    btnTrainModel.addEventListener('click', function () {
        btnTrainModel.setAttribute('data-kt-indicator', 'on');
        btnTrainModel.disabled = true;
        setStatus('Training Model', 'Model LBPH sedang dilatih ulang.', 'warning');

        axios.post("{{ route('pelanggaran-siswa.face-recognition.train') }}", {})
            .then(response => {
                const res = response.data;
                setStatus('Training Berhasil', res.message || 'Model berhasil dilatih ulang.', 'success');
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Model berhasil dilatih ulang.' });
                }
            })
            .catch(error => {
                const msg = error.response?.data?.message || 'Training model gagal.';
                setStatus('Training Gagal', msg, 'danger');
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Training Gagal', text: msg });
                }
            })
            .finally(() => {
                btnTrainModel.removeAttribute('data-kt-indicator');
                btnTrainModel.disabled = false;
            });
    });
});
</script>
@endpush
