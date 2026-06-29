@extends('layouts.app')

@section('title', 'Kamera Pelanggaran')
@section('page-title', 'Kamera Pelanggaran Siswa')

@section('breadcrumb')
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-gray-900">Kamera Pelanggaran</li>
</ul>
@endsection

@section('content')
<div class="row g-4 g-xl-8 guru-scan-layout">
    <!-- Camera/Scanner Section -->
    <div class="col-lg-6 col-xl-5 mb-5 mb-xl-0">
        <div class="card card-flush h-lg-100 shadow-sm border-0">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3 mb-1">Scanner Wajah</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Arahkan kamera ke wajah siswa</span>
                </h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" id="btn_toggle_camera" title="Nyalakan/Matikan Kamera">
                        <i class="ki-duotone ki-switch fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body pt-0 d-flex flex-column align-items-center">
                <!-- Camera Container -->
                <div class="position-relative w-100 bg-dark rounded-4 overflow-hidden mb-5 d-flex align-items-center justify-content-center" style="aspect-ratio: 4/3; min-height: 240px; box-shadow: inset 0 0 20px rgba(0,0,0,0.8);">
                    
                    <!-- Video stream -->
                    <video id="video_stream" autoplay playsinline class="w-100 h-100" style="object-fit: cover; display: none;"></video>

                    <!-- Voting progress overlay: circular SVG yang menampilkan
                         konsistensi hasil voting buffer secara real-time.
                         Hidden saat kamera nonaktif; ditunjukkan saat scanning. -->
                    <div id="voting_circle" class="voting-circle-overlay d-none" aria-live="polite">
                        <svg viewBox="0 0 120 120" width="180" height="180" aria-hidden="true">
                            <circle class="voting-circle-track" cx="60" cy="60" r="52"></circle>
                            <circle id="voting_circle_fill" class="voting-circle-fill" cx="60" cy="60" r="52"
                                    stroke-dasharray="326.7" stroke-dashoffset="326.7"></circle>
                        </svg>
                        <div class="voting-circle-label">
                            <span id="voting_circle_count">0/9</span>
                            <small id="voting_circle_status">Mengumpulkan…</small>
                        </div>
                    </div>

                    <!-- Camera Placeholder/Loader -->
                    <div id="camera_placeholder" class="text-center p-5 text-gray-500 d-flex flex-column align-items-center">
                        <div class="symbol symbol-60px symbol-circle mb-3 bg-light-dark d-flex align-items-center justify-content-center">
                            <i class="ki-duotone ki-camera text-gray-400 fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <h4 class="text-gray-400 fw-semibold fs-5 mb-1" id="placeholder_title">Kamera Nonaktif</h4>
                        <p class="text-muted fs-7 mb-4">Klik tombol di bawah untuk mengaktifkan pemindaian wajah</p>
                        <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill" id="btn_start_scanner">
                            <i class="ki-duotone ki-phone fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Aktifkan Kamera
                        </button>
                    </div>

                    <!-- Scanning Laser Overlay -->
                    <div id="scanner_laser" class="position-absolute w-100 start-0 top-0 d-none" style="height: 100%; pointer-events: none; z-index: 10;">
                        <!-- Border Box Frame -->
                        <div class="position-absolute start-50 top-50 translate-middle rounded-4 border border-primary border-3" style="width: 60%; height: 75%; opacity: 0.7; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45); border-style: dashed !important;">
                            <!-- Scanning vertical moving laser -->
                            <div class="position-absolute w-100 bg-primary opacity-75" style="height: 4px; box-shadow: 0 0 12px #3b82f6; animation: scanAnimation 2s linear infinite; top: 0;"></div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Canvas for frame extraction -->
                <canvas id="frame_canvas" class="d-none" width="320" height="240"></canvas>

                <!-- Scanning Status & Controls -->
                <div class="w-100">
                    <div id="scanner_status_alert" class="alert alert-dismissible bg-light-secondary border border-secondary d-flex flex-column flex-sm-row p-4 mb-4 align-items-center">
                        <i class="ki-duotone ki-information-3 fs-2hx text-gray-500 me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <h5 class="mb-1 text-gray-800 fw-bold fs-6" id="status_alert_title">Sistem Siap</h5>
                            <span class="fs-7 text-gray-600" id="status_alert_desc">Aktifkan kamera untuk memindai wajah siswa.</span>
                        </div>
                    </div>

                    <!-- Camera selector if multiple exist -->
                    <div class="d-flex flex-wrap gap-2 mb-4" id="quality_badges">
                        <span class="badge badge-light-secondary fw-semibold" id="quality_score_badge">Kualitas: -</span>
                        <span class="badge badge-light-secondary fw-semibold" id="brightness_badge">Cahaya: -</span>
                        <span class="badge badge-light-secondary fw-semibold" id="blur_badge">Stabil: -</span>
                    </div>

                    <div class="form-group mb-0 d-none" id="camera_selector_group">
                        <label class="fs-8 fw-semibold text-gray-700 mb-1">Pilih Sumber Kamera:</label>
                        <select id="camera_select" class="form-select form-select-sm form-select-solid"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Detail & Action Form -->
    <div class="col-lg-6 col-xl-7">
        <!-- Student Info & Log Card -->
        <div class="card card-flush shadow-sm border-0 h-lg-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3 mb-1">Detail Pelanggaran</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Siswa terdeteksi & pencatatan poin pelanggaran</span>
                </h3>
            </div>

            <!-- Pre-scan Placeholder -->
            <div class="card-body d-flex flex-column align-items-center justify-content-center min-h-350px" id="info_placeholder">
                <div class="symbol symbol-100px symbol-circle bg-light-primary mb-5 d-flex align-items-center justify-content-center">
                    <i class="ki-duotone ki-security-user text-primary fs-3x"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <h4 class="text-gray-800 fw-bold fs-4 mb-2">Belum Ada Wajah Terpindai</h4>
                <p class="text-muted text-center max-w-400px fs-6 mb-0 px-5">
                    Posisikan wajah siswa di depan kamera. Sistem akan mengenali wajah secara real-time dan memunculkan data siswa di sini secara otomatis.
                </p>
            </div>

            <!-- Scanned Student Result (Hidden initially) -->
            <div class="card-body pt-0 d-none" id="result_container">
                <!-- Student Quick Info -->
                <div class="d-flex flex-column flex-sm-row align-items-center bg-light-primary rounded-4 p-5 mb-8 border border-dashed border-primary">
                    <!-- Photo -->
                    <div class="symbol symbol-90px symbol-circle mb-4 mb-sm-0 me-0 me-sm-6 overflow-hidden border border-3 border-white shadow-sm bg-secondary">
                        <img id="student_photo" src="" alt="Foto Siswa" style="object-fit: cover; display: none; width: 90px; height: 90px;"/>
                        <div id="student_photo_fallback" class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary">
                            <i class="ki-duotone ki-user text-gray-500 fs-2x"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-grow-1 text-center text-sm-start">
                        <h4 class="text-gray-900 fw-bold fs-3 mb-1" id="student_name">-</h4>
                        <div class="text-muted fw-semibold fs-6 mb-3">
                            <span id="student_class">-</span> / <span id="student_major">-</span>
                        </div>
                        
                        <!-- Poin & Status Badges -->
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2">
                            <span class="badge badge-light-danger fw-bold fs-7 px-3 py-2 border border-danger border-opacity-20">
                                <i class="ki-duotone ki-medal-star text-danger fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                <span id="student_points">0</span> Poin
                            </span>
                            <span class="badge fw-bold fs-7 px-3 py-2" id="student_status_badge">-</span>
                        </div>

                        <!-- Match Strength (recognition confidence) -->
                        <div class="w-100 mt-4" id="match_strength_container">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold fs-7 text-gray-600 mb-0">
                                    <i class="ki-duotone ki-shield-search fs-7 me-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    Match Strength
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge fw-bold fs-8 px-2 py-1 bg-secondary" id="result_match_level">-</span>
                                    <span class="fw-bold fs-7 text-gray-700" id="result_match_percent">0%</span>
                                </div>
                            </div>
                            <div class="progress bg-light-primary" style="height: 10px;">
                                <div id="result_match_bar" class="progress-bar bg-secondary" role="progressbar" aria-label="Match strength" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted fs-8 mt-1 d-block">
                                Distance: <span id="result_distance">-</span>
                            </small>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <div class="mt-4 mt-sm-0">
                        <button type="button" class="btn btn-light-danger btn-sm border border-danger border-opacity-10 px-4" id="btn_reset_scanner">
                            <i class="ki-duotone ki-arrows-loop fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Scan Ulang
                        </button>
                    </div>
                </div>

                <!-- Log Violation Form -->
                <form id="form_catat_pelanggaran" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="siswa_id" id="form_siswa_id" value=""/>
                    
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label required fw-bold fs-6">Jenis Pelanggaran</label>
                        <div class="col-lg-9">
                            <select name="pelanggaran_id" id="select_pelanggaran" class="form-select form-select-solid form-select-lg" data-control="select2" data-placeholder="Pilih jenis pelanggaran">
                                <option value="">Pilih jenis pelanggaran</option>
                                @foreach($pelanggaranList as $p)
                                    <option value="{{ $p->id }}" data-poin="{{ $p->poin }}">
                                        [{{ $p->kode_pelanggaran }}] {{ $p->nama_pelanggaran }} - {{ $p->kategori->nama }} ({{ $p->poin }} Poin)
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7 mt-2">Pilih jenis pelanggaran yang dilakukan oleh siswa terpindai.</div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label required fw-bold fs-6">Tanggal</label>
                        <div class="col-lg-9">
                            <input type="date" name="tanggal_pelanggaran" class="form-control form-control-solid form-control-lg" value="{{ date('Y-m-d') }}" required/>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-bold fs-6">Catatan / Kronologi</label>
                        <div class="col-lg-9">
                            <textarea name="catatan" class="form-control form-control-solid form-control-lg" rows="3" placeholder="Ceritakan kronologi singkat (opsional)"></textarea>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-bold fs-6">Bukti Foto</label>
                        <div class="col-lg-9">
                            <input type="file" name="bukti" class="form-control form-control-solid form-control-lg" accept=".jpg,.jpeg,.png"/>
                            <div class="text-muted fs-7 mt-2">Format: JPG, JPEG, PNG. Ukuran maksimal 2MB (opsional).</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-3 mt-8">
                        <button type="button" class="btn btn-light" id="btn_cancel_form">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_violation">
                            <span class="indicator-label">
                                <i class="ki-duotone ki-notepad-bookmark fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan Pelanggaran
                            </span>
                            <span class="indicator-progress">
                                Mohon tunggu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush shadow-sm border-0 mt-5">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900 fs-4 mb-1">Riwayat Pelaporan</span>
            <span class="text-muted mt-1 fw-semibold fs-7">
                {{ auth()->user()->role === 'guru' ? '10 laporan terakhir yang Anda catat' : '10 laporan terbaru dari semua petugas' }}
            </span>
        </h3>
    </div>
    <div class="card-body pt-0">
        @if($riwayatGuru->isEmpty())
            <div class="text-center py-8">
                <i class="ki-duotone ki-note-2 text-gray-300 fs-3x"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                <div class="fw-bold text-gray-700 mt-3">Belum ada riwayat pelaporan</div>
                <div class="text-muted fs-7">Laporan dari halaman scan akan muncul di sini.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-4 mb-0">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-8 text-uppercase">
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @foreach($riwayatGuru as $item)
                            <tr>
                                <td class="text-nowrap">{{ optional($item->tanggal_pelanggaran)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <div class="fw-bold text-gray-900">{{ $item->siswa->nama ?? '-' }}</div>
                                    <div class="text-muted fs-8">{{ $item->siswa->kelas ?? '-' }} / {{ $item->siswa->jurusan ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->pelanggaran->nama_pelanggaran ?? '-' }}</div>
                                    <div class="text-muted fs-8">{{ $item->pelanggaran->kategori->nama ?? '-' }}</div>
                                </td>
                                <td><span class="badge badge-light-danger fw-bold">{{ $item->poin }} Poin</span></td>
                                <td><span class="badge badge-light-primary fw-bold">{{ $item->status_penanganan }}</span></td>
                                <td class="min-w-150px">{{ $item->catatan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
/* CSS Keyframes for Scan Effect animation */
@keyframes scanAnimation {
    0% {
        top: 0%;
    }
    50% {
        top: 100%;
    }
    100% {
        top: 0%;
    }
}

@media (max-width: 991.98px) {
    .guru-scan-layout .card {
        border-radius: 8px;
    }
    #kt_content_container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    #result_container .row > label {
        padding-bottom: 0.35rem;
    }
    #form_catat_pelanggaran .form-control-lg,
    #form_catat_pelanggaran .form-select-lg {
        min-height: 48px;
        font-size: 1rem;
    }
    #form_catat_pelanggaran .d-flex.justify-content-end {
        justify-content: stretch !important;
        flex-direction: column-reverse;
    }
    #form_catat_pelanggaran .btn {
        width: 100%;
        min-height: 48px;
    }
}

/* === Voting progress circle overlay ===
   SVG circular progress yang menampilkan konsistensi voting buffer
   secara real-time. Warna berubah sesuai kualitas voting. */
.voting-circle-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    pointer-events: none;
    z-index: 5;
}
.voting-circle-overlay.d-none { display: none !important; }

#voting_circle_fill {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 60px 60px;
    transition: stroke-dashoffset 220ms ease-out, stroke 180ms ease;
    stroke: #1abc9c;
}
.voting-circle-track {
    fill: none;
    stroke: rgba(255, 255, 255, 0.18);
    stroke-width: 8;
}
.voting-circle-label {
    position: absolute;
    text-align: center;
    color: #fff;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.65);
    font-weight: 600;
    letter-spacing: 0.4px;
}
.voting-circle-label span { font-size: 28px; display: block; }
.voting-circle-label small {
    font-size: 11px;
    opacity: 0.85;
    text-transform: uppercase;
    display: block;
    margin-top: 2px;
}

.voting-circle-overlay.is-warning #voting_circle_fill { stroke: #f1c40f; }
.voting-circle-overlay.is-danger  #voting_circle_fill { stroke: #e74c3c; }
.voting-circle-overlay.is-locked  #voting_circle_fill { stroke: #2ecc71; }
.voting-circle-overlay.is-locked  { animation: vc-pulse 1.2s ease-in-out 2; }
@keyframes vc-pulse {
    0%, 100% { transform: scale(1); }
    50%      { transform: scale(1.08); }
}
</style>
@endsection

@push('custom-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('video_stream');
    const canvas = document.getElementById('frame_canvas');
    const ctx = canvas.getContext('2d');
    
    // UI Elements
    const btnToggleCamera = document.getElementById('btn_toggle_camera');
    const btnStartScanner = document.getElementById('btn_start_scanner');
    const cameraPlaceholder = document.getElementById('camera_placeholder');
    const scannerLaser = document.getElementById('scanner_laser');
    
    const statusAlert = document.getElementById('scanner_status_alert');
    const statusAlertTitle = document.getElementById('status_alert_title');
    const statusAlertDesc = document.getElementById('status_alert_desc');
    const qualityScoreBadge = document.getElementById('quality_score_badge');
    const brightnessBadge = document.getElementById('brightness_badge');
    const blurBadge = document.getElementById('blur_badge');
    const cameraSelect = document.getElementById('camera_select');
    const cameraSelectorGroup = document.getElementById('camera_selector_group');
    
    // Form & Result Elements
    const infoPlaceholder = document.getElementById('info_placeholder');
    const resultContainer = document.getElementById('result_container');
    const btnResetScanner = document.getElementById('btn_reset_scanner');
    const btnCancelForm = document.getElementById('btn_cancel_form');
    
    const studentPhoto = document.getElementById('student_photo');
    const studentPhotoFallback = document.getElementById('student_photo_fallback');
    const studentName = document.getElementById('student_name');
    const studentClass = document.getElementById('student_class');
    const studentMajor = document.getElementById('student_major');
    const studentPoints = document.getElementById('student_points');
    const studentStatusBadge = document.getElementById('student_status_badge');
    const resultMatchBar = document.getElementById('result_match_bar');
    const resultMatchPercent = document.getElementById('result_match_percent');
    const resultMatchLevel = document.getElementById('result_match_level');
    const resultDistance = document.getElementById('result_distance');
    const formSiswaId = document.getElementById('form_siswa_id');
    const formCatatPelanggaran = document.getElementById('form_catat_pelanggaran');
    const selectPelanggaran = document.getElementById('select_pelanggaran');
    
    // State variables
    let stream = null;
    let scanInterval = null;
    let isScanningActive = false;
    let selectedCameraId = null;

    // Multi-frame voting state. Buffer pendek (6) + WIN_COUNT 4 = median lock
    // ~4-5s dengan scan interval 700ms. Margin 2 challenger tetap dipertahankan
    // untuk anti-flicker.
    const FRAME_BUFFER_SIZE = 6;
    const VOTE_MIN_WIN = 4;
    const STRICT_DISTANCE = 58.0;
    const MAX_DISTANCE_SPREAD = 12.0;
    const MIN_QUALITY_SCORE = 0.55;
    const MIN_CANDIDATE_MARGIN = 6.0;
    // Konstanta tambahan untuk voting upgrade: time-decay + confirmation hold.
    // Voting pass harus stabil selama VOTE_CONFIRM_HOLD_MS dan diulang
    // VOTE_CONFIRM_PASSES kali sebelum lock benar-benar fire.
    const VOTE_TIME_DECAY_MS = 2500;
    const VOTE_CONFIRM_HOLD_MS = 350;
    const VOTE_CONFIRM_PASSES = 2;
    // Scan interval adaptif: idle (tidak ada wajah) = IDLE_SCAN_INTERVAL_MS,
    // face terlihat = FAST_SCAN_INTERVAL_MS.
    const IDLE_SCAN_INTERVAL_MS = 1200;
    const FAST_SCAN_INTERVAL_MS = 650;
    let currentScanIntervalMs = IDLE_SCAN_INTERVAL_MS;
    let frameBuffer = []; // each entry: { studentId, distance, siswa, ts, ... }
    let lastVotedStudentId = null;
    let consecutivePasses = 0;
    let firstPassAt = 0;
    let lastFaceDetected = false; // adaptive scan interval hint

    // UI element untuk voting circle overlay
    const votingCircle = document.getElementById('voting_circle');
    const votingCircleFill = document.getElementById('voting_circle_fill');
    const votingCircleCount = document.getElementById('voting_circle_count');
    const votingCircleStatus = document.getElementById('voting_circle_status');

    // Voting circle driver: tampilkan/sembunyikan + update isi sesuai vote.
    function showVotingCircle() {
        if (votingCircle) votingCircle.classList.remove('d-none');
    }
    function hideVotingCircle() {
        if (!votingCircle) return;
        votingCircle.classList.add('d-none');
        votingCircle.classList.remove('is-warning', 'is-danger', 'is-locked');
    }
    function updateVotingCircle(vote) {
        if (!vote || !votingCircle) {
            hideVotingCircle();
            return;
        }
        showVotingCircle();
        votingCircle.classList.remove('is-warning', 'is-danger', 'is-locked');

        const total = vote.totalFrames || FRAME_BUFFER_SIZE;
        const ratio = Math.max(0, Math.min(1, vote.count / total));
        const offset = 326.7 * (1 - ratio);
        if (votingCircleFill) {
            votingCircleFill.setAttribute('stroke-dashoffset', offset.toFixed(2));
        }
        if (votingCircleCount) {
            votingCircleCount.textContent = `${vote.count}/${total}`;
        }

        let label = 'Mengumpulkan';
        if (vote.readyToLock) {
            label = 'Terkonfirmasi';
            votingCircle.classList.add('is-locked');
        } else if (
            vote.avgDistance >= STRICT_DISTANCE * 0.85
            || vote.distanceSpread > MAX_DISTANCE_SPREAD * 0.85
        ) {
            label = 'Belum stabil';
            votingCircle.classList.add('is-warning');
        } else if (
            vote.challengerCount > 0
            && (vote.count - vote.challengerCount) < 2
        ) {
            label = 'Kandidat ambigu';
            votingCircle.classList.add('is-danger');
        }
        if (votingCircleStatus) votingCircleStatus.textContent = label;
    }

    // Load available video devices
    async function getCameraDevices() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            cameraSelect.innerHTML = '';
            
            if (videoDevices.length > 0) {
                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Kamera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });
                
                if (videoDevices.length > 1) {
                    cameraSelectorGroup.classList.remove('d-none');
                }
                
                selectedCameraId = videoDevices[0].deviceId;
            }
        } catch (err) {
            console.error('Error listing camera devices:', err);
        }
    }

    // Start Webcam Stream
    async function startCamera() {
        try {
            if (stream) {
                stopCamera();
            }

            const constraints = {
                video: selectedCameraId ? { deviceId: { exact: selectedCameraId } } : true
            };

            updateStatus('Membuka Kamera...', 'Mengakses perangkat webcam anda.', 'secondary');
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            video.style.display = 'block';
            cameraPlaceholder.classList.add('d-none');
            scannerLaser.classList.remove('d-none');
            
            // Wait for video metadata to load
            video.onloadedmetadata = () => {
                video.play();
                startScanningLoop();
            };
            
            btnToggleCamera.classList.add('btn-light-success');
            btnToggleCamera.classList.remove('btn-color-primary');
        } catch (err) {
            console.error('Error starting camera:', err);
            updateStatus('Gagal Membuka Kamera', 'Pastikan izin akses kamera diberikan dan tidak digunakan oleh aplikasi lain.', 'danger');
            Swal.fire({
                icon: 'error',
                title: 'Akses Kamera Gagal',
                text: 'Sistem tidak dapat mengakses kamera Anda. Pastikan Anda mengizinkan akses kamera di browser.',
                confirmButtonText: 'OK'
            });
        }
    }

    // Stop Webcam Stream
    function stopCamera() {
        stopScanningLoop();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        video.style.display = 'none';
        cameraPlaceholder.classList.remove('d-none');
        scannerLaser.classList.add('d-none');
        
        btnToggleCamera.classList.remove('btn-light-success');
        btnToggleCamera.classList.add('btn-color-primary');
        updateStatus('Kamera Nonaktif', 'Klik Aktifkan Kamera untuk memulai.', 'secondary');
    }

    // Update Status Banner
    function updateStatus(title, desc, type) {
        statusAlertTitle.innerText = title;
        statusAlertDesc.innerText = desc;
        
        // Remove existing alert classes
        statusAlert.className = 'alert alert-dismissible d-flex flex-column flex-sm-row p-4 mb-4 align-items-center';
        
        if (type === 'success') {
            statusAlert.classList.add('bg-light-success', 'border-success');
            statusAlert.querySelector('i').className = 'ki-duotone ki-shield-tick fs-2hx text-success me-4 mb-5 mb-sm-0';
        } else if (type === 'danger') {
            statusAlert.classList.add('bg-light-danger', 'border-danger');
            statusAlert.querySelector('i').className = 'ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0';
        } else if (type === 'primary') {
            statusAlert.classList.add('bg-light-primary', 'border-primary');
            statusAlert.querySelector('i').className = 'ki-duotone ki-compass fs-2hx text-primary me-4 mb-5 mb-sm-0';
        } else if (type === 'warning') {
            statusAlert.classList.add('bg-light-warning', 'border-warning');
            statusAlert.querySelector('i').className = 'ki-duotone ki-information-5 fs-2hx text-warning me-4 mb-5 mb-sm-0';
        } else {
            statusAlert.classList.add('bg-light-secondary', 'border-secondary');
            statusAlert.querySelector('i').className = 'ki-duotone ki-information-3 fs-2hx text-gray-500 me-4 mb-5 mb-sm-0';
        }
    }

    function setBadgeState(el, label, value, type = 'secondary') {
        if (!el) return;
        el.innerText = `${label}: ${value}`;
        el.classList.remove('badge-light-secondary', 'badge-light-success', 'badge-light-warning', 'badge-light-danger');
        el.classList.add(`badge-light-${type}`);
    }

    function updateQualityUI(res) {
        const quality = Number(res.quality_score ?? 0);
        const brightness = res.brightness;
        const blurScore = res.blur_score;

        if (res.face_detected === false) {
            setBadgeState(qualityScoreBadge, 'Kualitas', 'wajah belum terbaca', 'warning');
            setBadgeState(brightnessBadge, 'Cahaya', '-', 'secondary');
            setBadgeState(blurBadge, 'Stabil', '-', 'secondary');
            return;
        }

        const qualityPct = Math.round(Math.max(0, Math.min(1, quality)) * 100);
        setBadgeState(
            qualityScoreBadge,
            'Kualitas',
            `${qualityPct}%`,
            quality >= 0.75 ? 'success' : (quality >= MIN_QUALITY_SCORE ? 'warning' : 'danger')
        );

        if (brightness == null) {
            setBadgeState(brightnessBadge, 'Cahaya', '-', 'secondary');
        } else {
            const brightValue = Number(brightness).toFixed(0);
            setBadgeState(
                brightnessBadge,
                'Cahaya',
                brightValue,
                brightness >= 45 && brightness <= 215 ? 'success' : 'danger'
            );
        }

        if (blurScore == null) {
            setBadgeState(blurBadge, 'Stabil', '-', 'secondary');
        } else {
            const blurValue = Number(blurScore).toFixed(0);
            setBadgeState(blurBadge, 'Stabil', blurValue, blurScore >= 35 ? 'success' : 'danger');
        }
    }

    function qualityMessage(res) {
        if (Array.isArray(res.reject_reasons) && res.reject_reasons.length > 0) {
            return res.reject_reasons.join(' ');
        }
        return res.message || 'Pastikan wajah menghadap kamera, cukup terang, dan tidak terlalu jauh.';
    }

    // Start continuous scan check every 1.5 seconds
    function startScanningLoop() {
        if (isScanningActive) return;
        isScanningActive = true;
        // Reset voting buffer setiap kali scan dimulai ulang.
        frameBuffer = [];
        lastFaceDetected = false;
        currentScanIntervalMs = IDLE_SCAN_INTERVAL_MS;
        updateStatus('Memindai...', 'Sistem sedang menganalisis wajah secara real-time.', 'primary');

        scheduleNextScan(0);
    }

    // Adaptive scheduler: setTimeout rekursif yang auto-tune interval berdasarkan
    // apakah wajah terdeteksi. Lebih cepat tanggap dibanding setInterval karena
    // bisa langsung jadwalkan scan berikutnya begitu response datang — kita
    // tunggu response + interval adaptif (bukan interval + response), jadi total
    // cycle = max(interval, response_latency).
    function scheduleNextScan(delayMs) {
        if (!isScanningActive) return;
        scanInterval = setTimeout(() => {
            scanInterval = null;
            captureAndScan();
        }, delayMs);
    }

    // Update interval: naik ke fast-track saat face terdeteksi, turun ke idle
    // saat tidak ada (hemat CPU + HTTP request).
    function updateScanInterval(faceVisible) {
        const target = faceVisible ? FAST_SCAN_INTERVAL_MS : IDLE_SCAN_INTERVAL_MS;
        if (target === currentScanIntervalMs) return;
        currentScanIntervalMs = target;
    }

    // Stop scanning loop
    function stopScanningLoop() {
        isScanningActive = false;
        if (scanInterval) {
            clearTimeout(scanInterval);
            scanInterval = null;
        }
    }

    // Reset voting buffer (dipakai saat reset scanner atau setelah match terkunci)
    function clearFrameBuffer() {
        frameBuffer = [];
        consecutivePasses = 0;
        firstPassAt = 0;
        hideVotingCircle();
    }

    // Voting multi-frame: hitung label yang paling sering muncul dengan avg distance < 60.
    // Return {studentId, count, avgDistance, readyToLock} atau null jika belum siap.
    function evaluateFrameBuffer() {
        if (frameBuffer.length === 0) return null;

        // Time-decay: drop frame lebih lama dari VOTE_TIME_DECAY_MS (stale frames
        // tidak boleh ikut voting). Scan interval 1.5s, decay 2.5s = max ~2
        // frame aktif per kali — pemenang tetap harus konsisten lintas scan.
        const now = performance.now();
        frameBuffer = frameBuffer.filter(f => {
            const ts = typeof f.ts === 'number' ? f.ts : now;
            return (now - ts) <= VOTE_TIME_DECAY_MS;
        });
        if (frameBuffer.length === 0) return null;

        // Group by student_id
        const groups = {};
        for (const f of frameBuffer) {
            if (f.studentId == null) continue;
            if (!groups[f.studentId]) {
                groups[f.studentId] = { count: 0, sumDistance: 0, distances: [] };
            }
            groups[f.studentId].count += 1;
            groups[f.studentId].sumDistance += f.distance;
            groups[f.studentId].distances.push(f.distance);
        }

        // Cari label dengan count tertinggi
        let winnerId = null;
        let winnerCount = 0;
        let winnerInfo = null;
        for (const [sid, info] of Object.entries(groups)) {
            if (info.count > winnerCount) {
                winnerId = parseInt(sid, 10);
                winnerCount = info.count;
                winnerInfo = info;
            }
        }

        if (winnerId == null) return null;

        const avgDistance = winnerInfo.sumDistance / winnerCount;
        const distanceSpread = Math.max(...winnerInfo.distances) - Math.min(...winnerInfo.distances);
        const challenger = Object.entries(groups)
            .filter(([sid]) => parseInt(sid, 10) !== winnerId)
            .sort((a, b) => b[1].count - a[1].count)[0] || null;
        const challengerCount = challenger ? challenger[1].count : 0;
        const stableMajority = winnerCount >= VOTE_MIN_WIN && (winnerCount - challengerCount) >= 2;
        const readyToLock = stableMajority && avgDistance < STRICT_DISTANCE && distanceSpread <= MAX_DISTANCE_SPREAD;

        return {
            studentId: winnerId,
            count: winnerCount,
            avgDistance: avgDistance,
            distanceSpread: distanceSpread,
            challengerCount: challengerCount,
            totalFrames: frameBuffer.length,
            readyToLock: readyToLock
        };
    }

    // Tampilkan status voting ke user
    function updateVotingStatus(vote) {
        if (!vote) {
            updateStatus('Memindai...', 'Sistem sedang menganalisis wajah secara real-time.', 'primary');
            return;
        }
        if (vote.readyToLock) {
            updateStatus('Konfirmasi Terpenuhi',
                `Konsisten: ${vote.count}/${vote.totalFrames} frame cocok (avg distance ${vote.avgDistance.toFixed(1)}).`,
                'success');
        } else {
            updateStatus('Konfirmasi identitas',
                `Sedang mengonfirmasi: ${vote.count}/${vote.totalFrames} frame cocok, avg distance ${vote.avgDistance.toFixed(1)}.`,
                'primary');
        }
    }

    // Capture Frame and send to Server for recognition.
    // Strategi overlap: jadwalkan scan berikut LANGSUNG setelah request terkirim,
    // bukan menunggu response. Response datang di callback lalu update interval
    // adaptif. Total cycle = max(interval, response_latency) — biasanya response
    // latency > interval jadi kita tidak idle menunggu response.
    function captureAndScan() {
        if (!isScanningActive || !stream) return;

        // Anti-overlap guard: kalau ada request yang masih in-flight, skip cycle ini
        // (lebih aman daripada menumpuk antrian axios).
        if (window.__scanInFlight) {
            scheduleNextScan(currentScanIntervalMs);
            return;
        }
        window.__scanInFlight = true;

        // Draw current video frame ke hidden canvas (320×240 — payload 6× lebih
        // kecil dari 640×480. Haar Cascade robust di 320×240, akurasi tetap.)
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // JPEG quality 0.7 — bandwidth turun ~40% vs 0.85, visually tetap readable.
        const base64Image = canvas.toDataURL('image/jpeg', 0.7);

        // Schedule scan berikut segera setelah request terkirim (overlap).
        // Kita pakai setTimeout recursive (bukan setInterval) supaya bisa tune
        // interval adaptif tiap response.
        scheduleNextScan(currentScanIntervalMs);

        // Kirim ke Laravel controller via AJAX.
        axios.post("{{ route('guru.face-recognition.scan') }}", {
            image: base64Image
        })
        .then(response => {
            window.__scanInFlight = false;
            const res = response.data;
            updateQualityUI(res);

            // Jika service melaporkan model belum dilatih, hentikan scanning
            if (res.message && res.message.includes('belum dilatih')) {
                stopCamera();
                Swal.fire({
                    icon: 'warning',
                    title: 'Model Belum Dilatih',
                    text: res.message,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Jika service error/koneksi mati
            if (!res.success) {
                updateStatus('Koneksi Error', res.message || 'Gagal terhubung ke service face recognition.', 'danger');
                // Slow down jika error/hang supaya tidak spam request.
                updateScanInterval(false);
                return;
            }

            // Adaptive interval: ada wajah di frame = fast, tidak ada = idle.
            const faceVisible = !!res.face_detected;
            updateScanInterval(faceVisible);
            lastFaceDetected = faceVisible;

            // Wajah tidak terdeteksi / kualitas kurang: reset buffer (mulai ulang konfirmasi)
            if (!res.recognized) {
                if (res.message && res.message.includes('Wajah tidak terdeteksi')) {
                    clearFrameBuffer();
                    updateStatus('Memindai...', 'Posisikan wajah siswa dengan jelas di area kamera.', 'primary');
                } else if (res.match_level === 'quality_reject' || (Array.isArray(res.reject_reasons) && res.reject_reasons.length > 0)) {
                    clearFrameBuffer();
                    updateStatus('Kualitas Scan Kurang', qualityMessage(res), 'warning');
                } else if (res.match_level === 'ambiguous') {
                    clearFrameBuffer();
                    updateStatus('Wajah Belum Stabil', res.message || 'Kandidat wajah terlalu dekat. Ulangi scan dengan posisi lebih stabil.', 'warning');
                } else if (res.message && res.message.includes('tidak dikenali')) {
                    // Wajah terdeteksi tapi tidak cocok: anggap sebagai "noisy frame"
                    // masukkan null student ke buffer agar voting tahu ada frame gagal.
                    frameBuffer.push({ studentId: null, distance: 999, siswa: null, recognized: false, ts: performance.now() });
                    if (frameBuffer.length > FRAME_BUFFER_SIZE) frameBuffer.shift();
                    const vote = evaluateFrameBuffer();
                    updateVotingStatus(vote);
                    updateVotingCircle(vote);
                } else {
                    updateStatus('Wajah Tidak Dikenali', res.message || 'Wajah tidak cocok dengan database siswa.', 'danger');
                }
                return;
            }

            // Recognized: masukkan ke buffer voting.
            // Scan endpoint mengembalikan { matched, siswa: {id, nama, ...} } saat match.
            // top_match adalah signature baru dari Python service, siswa adalah data lengkap.
            const studentId = (res.top_match && res.top_match.student_id) ?? res.student_id ?? null;
            // Kontrak Python v2: field 'confidence' sudah tidak ada. Pakai distance
            // (top-level hasil normalisasi Service, atau fallback ke top_match.distance).
            const distance = res.distance ?? (res.top_match && res.top_match.distance) ?? 999;
            const matchStrength = res.match_strength ?? (res.top_match && res.top_match.match_strength) ?? 0;
            const matchLevel = res.match_level ?? (res.top_match && res.top_match.match_level) ?? null;
            const candidateMargin = res.candidate_margin ?? null;
            const siswa = res.siswa ?? null;

            if (studentId == null) {
                updateStatus('Wajah Tidak Dikenali', res.message || 'Format ID siswa tidak valid.', 'danger');
                return;
            }

            if (Number(res.quality_score ?? 1) < MIN_QUALITY_SCORE) {
                clearFrameBuffer();
                updateStatus('Kualitas Scan Kurang', qualityMessage(res), 'warning');
                return;
            }

            if (candidateMargin !== null && Number(candidateMargin) < MIN_CANDIDATE_MARGIN && matchLevel !== 'strict') {
                clearFrameBuffer();
                updateStatus('Wajah Belum Stabil', 'Kandidat wajah terlalu dekat. Dekatkan wajah dan ulangi scan.', 'warning');
                return;
            }

            frameBuffer.push({ studentId: studentId, distance: distance, matchStrength: matchStrength, matchLevel: matchLevel, candidateMargin: candidateMargin, siswa: siswa, recognized: true, ts: performance.now() });
            if (frameBuffer.length > FRAME_BUFFER_SIZE) frameBuffer.shift();

            // Live preview match strength dari frame terakhir (feedback real-time
            // ke user). Setelah lock, lockStudentMatch akan override dengan
            // rata-rata semua frame winner (lebih stabil).
            updateMatchStrengthUI(matchStrength, distance, matchLevel);

            const vote = evaluateFrameBuffer();
            updateVotingStatus(vote);
            updateVotingCircle(vote);

            // Lock hanya jika voting majority + avg distance strict DAN
            // confirmation hold terpenuhi (VOTE_CONFIRM_PASSES × VOTE_CONFIRM_HOLD_MS).
            // Tanpa hold, satu cluster pass langsung lock — hold mencegah
            // lock prematur saat wajah baru muncul di area scan.
            const nowMs = performance.now();
            if (vote && vote.readyToLock) {
                if (consecutivePasses === 0) {
                    firstPassAt = nowMs;
                }
                if (nowMs - firstPassAt >= VOTE_CONFIRM_HOLD_MS) {
                    consecutivePasses++;
                } else {
                    consecutivePasses = 1;
                }
            } else {
                consecutivePasses = 0;
                firstPassAt = 0;
            }

            if (consecutivePasses >= VOTE_CONFIRM_PASSES
                && vote
                && vote.studentId !== lastVotedStudentId) {
                consecutivePasses = 0;
                firstPassAt = 0;
                lockStudentMatch(vote.studentId);
            }
        })
        .catch(error => {
            window.__scanInFlight = false;
            console.error('AJAX scan error:', error);
            const msg = error.response?.data?.message || 'Gagal terhubung ke service face recognition.';
            updateStatus('Koneksi Error', msg, 'danger');
        });
    }

    // Lock hasil match: ambil data siswa dari frame buffer (winner).
    // Karena scan endpoint mengembalikan data siswa lengkap, kita bisa pakai
    // data siswa dari salah satu entry winner di buffer.
    function lockStudentMatch(studentId) {
        stopScanningLoop();
        lastVotedStudentId = studentId;

        // Tampilkan state "locked" (lingkaran hijau pulse) sebentar sebagai
        // feedback visual ke operator bahwa voting sudah terkonfirmasi.
        if (votingCircle && !votingCircle.classList.contains('d-none')) {
            votingCircle.classList.add('is-locked');
            votingCircle.classList.remove('is-warning', 'is-danger');
            setTimeout(() => hideVotingCircle(), 1500);
        }
        consecutivePasses = 0;
        firstPassAt = 0;

        // Cari data siswa dari frame buffer
        const winnerEntries = frameBuffer.filter(f => f.studentId === studentId && f.siswa);
        const winnerEntry = winnerEntries[winnerEntries.length - 1] || null;
        // Rata-rata match_strength dari semua frame winner (lebih stabil dari single frame)
        const winnerStrengths = winnerEntries.map(e => e.matchStrength).filter(v => v != null && !isNaN(v));
        const avgStrength = winnerStrengths.length
            ? winnerStrengths.reduce((a, b) => a + b, 0) / winnerStrengths.length
            : null;
        const winnerDistances = winnerEntries.map(e => e.distance).filter(v => v != null && !isNaN(v));
        const avgDistance = winnerDistances.length
            ? winnerDistances.reduce((a, b) => a + b, 0) / winnerDistances.length
            : null;
        // match_level: pakai dari entry terakhir, fallback ke strict jika avg strength tinggi
        let winnerLevel = winnerEntry && winnerEntry.matchLevel ? winnerEntry.matchLevel : null;
        if (!winnerLevel && avgStrength != null) {
            winnerLevel = avgStrength >= 0.9 ? 'strict' : (avgStrength >= 0.5 ? 'loose' : 'no_match');
        }

        if (winnerEntry && winnerEntry.siswa) {
            populateStudentUI(winnerEntry.siswa, avgStrength, avgDistance, winnerLevel);
        } else {
            // Fallback: scan endpoint tidak return data siswa lengkap.
            // Hal ini bisa terjadi jika signature Python berubah. Tampilkan error
            // yang jelas agar user tahu apa yang salah.
            toastr.error('Data siswa tidak tersedia. Silakan scan ulang.');
            updateStatus('Error Konfirmasi', 'Data siswa tidak tersedia setelah voting.', 'danger');
        }
    }

    // Update visual indicator untuk match_strength (recognition confidence).
    // - matchStrength: float 0.0–1.0 dari Python service, atau null/tidak ada.
    // - distance: nilai distance mentah dari response (untuk label).
    // - matchLevel: 'strict' | 'loose' | 'no_match' (opsional).
    // Threshold warna:
    //   >= 0.9  -> success (hijau, sangat yakin)
    //   >= 0.5  -> warning (kuning, cukup yakin)
    //   <  0.5  -> danger  (merah, rendah)
    function updateMatchStrengthUI(matchStrength, distance, matchLevel) {
        // Update bar
        if (matchStrength == null || isNaN(matchStrength)) {
            resultMatchPercent.innerText = '-';
            resultMatchBar.style.width = '0%';
            resultMatchBar.setAttribute('aria-valuenow', 0);
            resultMatchBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            resultMatchBar.classList.add('bg-secondary');
        } else {
            const pct = Math.max(0, Math.min(100, Math.round(matchStrength * 100)));
            resultMatchPercent.innerText = pct + '%';
            resultMatchBar.style.width = pct + '%';
            resultMatchBar.setAttribute('aria-valuenow', pct);

            resultMatchBar.classList.remove('bg-secondary', 'bg-success', 'bg-warning', 'bg-danger');
            if (matchStrength >= 0.9) {
                resultMatchBar.classList.add('bg-success');
            } else if (matchStrength >= 0.5) {
                resultMatchBar.classList.add('bg-warning');
            } else {
                resultMatchBar.classList.add('bg-danger');
            }
        }

        // Update distance label
        if (distance != null && !isNaN(distance)) {
            resultDistance.innerText = Number(distance).toFixed(1);
        } else {
            resultDistance.innerText = '-';
        }

        // Update level badge (STRICT / LOOSE / NO MATCH / -)
        let levelText = '-';
        let levelClass = 'bg-secondary';
        if (matchLevel === 'strict') {
            levelText = 'STRICT';
            levelClass = 'bg-success';
        } else if (matchLevel === 'loose') {
            levelText = 'LOOSE';
            levelClass = 'bg-warning';
        } else if (matchLevel === 'no_match' || matchLevel === 'no-match' || matchLevel === 'nomatch') {
            levelText = 'NO MATCH';
            levelClass = 'bg-danger';
        }
        resultMatchLevel.innerText = levelText;
        resultMatchLevel.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'bg-secondary');
        resultMatchLevel.classList.add(levelClass);
    }

    // Reset visual match strength indicator ke kondisi default (digunakan di reset).
    function resetMatchStrengthUI() {
        updateMatchStrengthUI(null, null, null);
    }

    // Populate UI dengan data siswa
    function populateStudentUI(siswa, matchStrength, distance, matchLevel) {
        studentName.innerText = siswa.nama;
        studentClass.innerText = siswa.kelas;
        studentMajor.innerText = siswa.jurusan;
        studentPoints.innerText = siswa.total_poin;
        formSiswaId.value = siswa.id;

        if (siswa.foto) {
            studentPhoto.src = siswa.foto;
            studentPhoto.style.display = 'block';
            studentPhotoFallback.style.display = 'none';
        } else {
            studentPhoto.style.display = 'none';
            studentPhotoFallback.style.display = 'flex';
        }

        studentStatusBadge.innerText = siswa.status_pembinaan;
        studentStatusBadge.className = `badge fw-bold fs-7 px-3 py-2 bg-light-${siswa.status_badge} text-${siswa.status_badge} border border-${siswa.status_badge} border-opacity-20`;

        // Render match strength visual (progress bar + level badge + distance label).
        // Nilai null/undefined akan di-render sebagai '-' dengan warna abu-abu.
        updateMatchStrengthUI(matchStrength, distance, matchLevel);

        infoPlaceholder.classList.add('d-none');
        resultContainer.classList.remove('d-none');

        updateStatus('Pemindaian Sukses', `Siswa dikenali: ${siswa.nama} (konfirmasi multi-frame)`, 'success');

        playBeep(800, 150, 0.1);
        toastr.success(`Siswa berhasil dikenali: ${siswa.nama}`);
    }

    // AudioContext singleton — instansiasi satu kali per sesi, jauh lebih
    // murah daripada create new setiap beep (yang lama di lockStudentMatch).
    let _audioCtx = null;
    function getAudioCtx() {
        if (!_audioCtx) {
            try {
                _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {
                return null;
            }
        }
        // Beberapa browser suspend AudioContext jika tidak ada user gesture.
        if (_audioCtx && _audioCtx.state === 'suspended') {
            _audioCtx.resume();
        }
        return _audioCtx;
    }

    // Play Beep Sound using Web Audio API
    function playBeep(freq, duration, vol) {
        try {
            const audioCtx = getAudioCtx();
            if (!audioCtx) return;
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.frequency.value = freq;
            gainNode.gain.setValueAtTime(vol, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration/1000);

            oscillator.start(audioCtx.currentTime);
            oscillator.stop(audioCtx.currentTime + duration/1000);
        } catch (e) {
            console.log("Audio not supported or interaction required");
        }
    }

    // Reset scanner state to try again
    function resetScanner() {
        // Clear student details
        formSiswaId.value = '';
        studentName.innerText = '-';
        studentClass.innerText = '-';
        studentMajor.innerText = '-';
        studentPoints.innerText = '0';
        studentPhoto.src = '';
        studentPhoto.style.display = 'none';
        studentPhotoFallback.style.display = 'flex';

        // Reset match strength indicator
        resetMatchStrengthUI();
        setBadgeState(qualityScoreBadge, 'Kualitas', '-', 'secondary');
        setBadgeState(brightnessBadge, 'Cahaya', '-', 'secondary');
        setBadgeState(blurBadge, 'Stabil', '-', 'secondary');

        // Reset form controls
        formCatatPelanggaran.reset();
        if (typeof $ !== 'undefined' && $('#select_pelanggaran').data('select2')) {
            $('#select_pelanggaran').val('').trigger('change');
        }

        // Toggle visibility
        resultContainer.classList.add('d-none');
        infoPlaceholder.classList.remove('d-none');

        // Reset voting state
        clearFrameBuffer();
        lastVotedStudentId = null;

        // Resume scanning if camera is active
        if (stream) {
            startScanningLoop();
        } else {
            startCamera();
        }
    }

    // Click event listeners
    btnStartScanner.addEventListener('click', startCamera);
    btnToggleCamera.addEventListener('click', function () {
        if (stream) {
            stopCamera();
        } else {
            startCamera();
        }
    });

    btnResetScanner.addEventListener('click', resetScanner);
    btnCancelForm.addEventListener('click', resetScanner);

    // Watch for camera selection change
    cameraSelect.addEventListener('change', function () {
        selectedCameraId = this.value;
        if (stream) {
            startCamera();
        }
    });

    // Check device list on start
    getCameraDevices();

    // Form Submit Handler
    formCatatPelanggaran.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('btn_submit_violation');
        submitBtn.setAttribute('data-kt-indicator', 'on');
        submitBtn.disabled = true;

        const formData = new FormData(this);

        axios.post("{{ route('guru.pelanggaran-siswa.store-from-face') }}", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;

            if (response.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: response.data.message || 'Pelanggaran siswa berhasil disimpan!',
                    confirmButtonText: 'Lanjut Pindai'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: response.data.message || 'Terjadi kesalahan saat menyimpan data.',
                    confirmButtonText: 'Coba Lagi'
                });
            }
        })
        .catch(error => {
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;
            
            console.error('Violation submit error:', error);
            
            let errorText = 'Gagal menyimpan pelanggaran. Silakan periksa koneksi Anda.';
            if (error.response?.data?.errors) {
                const errors = error.response.data.errors;
                errorText = Object.values(errors).flat().join('\n');
            } else if (error.response?.data?.message) {
                errorText = error.response.data.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: errorText,
                confirmButtonText: 'OK'
            });
        });
    });

    // Initialize Select2 if present
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#select_pelanggaran').select2({
            placeholder: "Pilih jenis pelanggaran",
            allowClear: true
        });
    }
});
</script>
@endpush
