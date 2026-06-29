# Plan: Lingkaran Voting + Auto-Restart Supervisor

## Overview

Meningkatkan keyakinan operator saat face recognition berjalan di kamera pelanggaran
dengan **lingkaran progress voting** yang menampilkan konsistensi hasil recognition
secara real-time, **memperketat voting logic** untuk akurasi lock yang lebih tinggi,
dan **menambahkan supervisor wrapper** agar Python FR service auto-restart jika crash.

## Goals (sesuai jawaban user)

1. **Circular progress overlay di tengah video** — mengikuti gaya `page_recognize.html`
   Python service. SVG circle yang fill sesuai jumlah vote yang terkumpul dari buffer.
2. **Tingkatkan akurasi voting** — buffer 9-frame dengan strict majority sudah ada;
   tambahkan time-decay buffer, confirmation hold (debounce), dan second-pass
   confirmation.
3. **Supervisor wrapper di `run.bat`** — restart otomatis jika Python exit,
   log ke `logs/service.log`, max restart dengan backoff.

## Non-Goals

- Tidak membuat WebSocket/SSE infrastructure dari Laravel — voting tetap di-track
  client-side (JS), lock tetap di-decide di browser.
- Tidak ubah pipeline Python atau API kontrak — test `FaceRecognitionPipelineV2Test`
  harus tetap pass tanpa modifikasi.

## Current State (read dari codebase)

JS voting **sudah matang** di `resources/views/guru/attendance/index.blade.php`:

```js
// Line 376-382 (existing, tidak diubah nama var)
const FRAME_BUFFER_SIZE = 9;
const VOTE_MIN_WIN = 6;
const STRICT_DISTANCE = 58.0;
const MAX_DISTANCE_SPREAD = 12.0;
const MIN_QUALITY_SCORE = 0.55;
const MIN_CANDIDATE_MARGIN = 6.0;
let frameBuffer = [];
```

Yang sudah ada:
- Voting buffer 9 frame, lock butuh 6+ vote winner
- Avg distance < 58 dan spread ≤ 12
- Candidate margin minimal 6
- Quality gate 0.55

Yang **belum ada** (akan ditambah):
- **Time-decay** — frame lebih tua dari N ms dibuang
- **Confirmation hold** — voting pass harus stabil selama window tertentu
  sebelum lock fire (debounce)
- **Second-pass confirmation** — butuh 2 consecutive pass (setelah hold)
- **Visual lingkaran progress** — saat ini hanya text status

## Files to Change

### 1. [resources/views/guru/attendance/index.blade.php](resources/views/guru/attendance/index.blade.php) — UI Lingkaran + Voting Upgrade

JS **inline** di blade (di dalam `@push('custom-js')` setelah `</script>` di akhir
file). Tidak ada `fr-scan.js` terpisah. Edit di tempat.

**a) Tambah konstanta voting (setelah line 382, di samping existing constants):**

```js
// Konstanta tambahan untuk voting upgrade
const VOTE_TIME_DECAY_MS = 2500;     // frame > 2.5s dibuang
const VOTE_CONFIRM_HOLD_MS = 350;    // voting pass harus stabil 350ms
const VOTE_CONFIRM_PASSES = 2;       // butuh 2 pass setelah hold untuk lock
```

**b) Tambah state untuk confirmation:**

```js
let consecutivePasses = 0;
let firstPassAt = 0;
```

**c) Modifikasi `frameBuffer.push` agar include timestamp:**

```js
// Ganti semua frameBuffer.push({...}) existing dengan:
frameBuffer.push({
  studentId: studentId,
  distance: distance,
  matchStrength: matchStrength,
  matchLevel: matchLevel,
  candidateMargin: candidateMargin,
  siswa: siswa,
  recognized: true,
  ts: performance.now(),  // ← BARU
});
```

**d) Tambah filter time-decay di `evaluateFrameBuffer()` (sebelum grouping):**

```js
function evaluateFrameBuffer() {
  if (frameBuffer.length === 0) return null;

  // Time-decay: drop frame lebih lama dari VOTE_TIME_DECAY_MS
  const now = performance.now();
  frameBuffer = frameBuffer.filter(f => now - (f.ts ?? 0) <= VOTE_TIME_DECAY_MS);
  if (frameBuffer.length === 0) return null;

  // ... existing grouping + winner detection tetap sama
}
```

**e) Modifikasi `captureAndScan` agar voting pass tidak langsung lock:**

```js
// Ganti block ini (sekitar line 740-743):
//   if (vote && vote.readyToLock && vote.studentId !== lastVotedStudentId) {
//       lockStudentMatch(vote.studentId);
//   }

// Menjadi:
const nowMs = performance.now();
if (vote && vote.readyToLock) {
  if (consecutivePasses === 0) firstPassAt = nowMs;
  if (nowMs - firstPassAt >= VOTE_CONFIRM_HOLD_MS) {
    consecutivePasses++;
  } else {
    consecutivePasses = 1;
  }
} else {
  consecutivePasses = 0;
}

if (consecutivePasses >= VOTE_CONFIRM_PASSES
    && vote
    && vote.studentId !== lastVotedStudentId) {
  consecutivePasses = 0;
  lockStudentMatch(vote.studentId);
}
```

**f) Reset `consecutivePasses` di `clearFrameBuffer()`:**

```js
function clearFrameBuffer() {
  frameBuffer = [];
  consecutivePasses = 0;
  firstPassAt = 0;
  hideVotingCircle();  // ← reset visual
}
```

**g) Tambah SVG lingkaran progress (setelah `<video>` element, sebelum scanner laser):**

```html
<!-- Overlay voting progress — circular SVG yang fill sesuai jumlah vote terkumpul -->
<div id="voting-circle" class="voting-circle-overlay d-none" aria-live="polite">
  <svg viewBox="0 0 120 120" width="180" height="180" aria-hidden="true">
    <circle class="voting-circle-track" cx="60" cy="60" r="52" />
    <circle id="voting-circle-fill" class="voting-circle-fill" cx="60" cy="60" r="52"
            stroke-dasharray="326.7" stroke-dashoffset="326.7" />
  </svg>
  <div class="voting-circle-label">
    <span id="voting-circle-count">0/9</span>
    <small id="voting-circle-status">Mengumpulkan…</small>
  </div>
</div>
```

**h) Tambah CSS `@push('custom-css')`:**

```css
:root {
  --vc-stroke: 8;
  --vc-radius: 52;
  --vc-circ: 326.7; /* 2 * PI * 52 */
  --vc-color-track: rgba(255, 255, 255, 0.18);
  --vc-color-fill: #1abc9c;
  --vc-color-warning: #f1c40f;
  --vc-color-danger: #e74c3c;
  --vc-color-locked: #2ecc71;
}

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

#voting-circle-fill {
  fill: none;
  stroke-width: var(--vc-stroke);
  stroke-linecap: round;
  transform: rotate(-90deg);
  transform-origin: 60px 60px;
  transition: stroke-dashoffset 220ms ease-out, stroke 180ms ease;
  stroke: var(--vc-color-fill);
}
.voting-circle-track {
  fill: none;
  stroke: var(--vc-color-track);
  stroke-width: var(--vc-stroke);
}
.voting-circle-label {
  position: absolute;
  text-align: center;
  color: #fff;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.65);
  font-weight: 600;
}
.voting-circle-label span { font-size: 28px; display: block; }
.voting-circle-label small { font-size: 11px; opacity: 0.85; text-transform: uppercase; }

.voting-circle-overlay.is-warning #voting-circle-fill { stroke: var(--vc-color-warning); }
.voting-circle-overlay.is-danger  #voting-circle-fill { stroke: var(--vc-color-danger); }
.voting-circle-overlay.is-locked  #voting-circle-fill { stroke: var(--vc-color-locked); }
.voting-circle-overlay.is-locked  { animation: vc-pulse 1.2s ease-in-out 2; }
@keyframes vc-pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
```

**i) Tambah driver lingkaran (di `@push('custom-js')`):**

```js
function showVotingCircle() {
  document.getElementById('voting-circle').classList.remove('d-none');
}
function hideVotingCircle() {
  const el = document.getElementById('voting-circle');
  el.classList.add('d-none');
  el.classList.remove('is-warning', 'is-danger', 'is-locked');
}
function updateVotingCircle(vote) {
  const overlay = document.getElementById('voting-circle');
  const fill = document.getElementById('voting-circle-fill');
  const countEl = document.getElementById('voting-circle-count');
  const statusEl = document.getElementById('voting-circle-status');

  if (!vote) {
    hideVotingCircle();
    return;
  }

  showVotingCircle();
  overlay.classList.remove('is-warning', 'is-danger', 'is-locked');

  const total = vote.totalFrames || FRAME_BUFFER_SIZE;
  const ratio = Math.max(0, Math.min(1, vote.count / total));
  const offset = 326.7 * (1 - ratio);
  fill.setAttribute('stroke-dashoffset', offset.toFixed(2));

  countEl.textContent = `${vote.count}/${total}`;

  if (vote.readyToLock) {
    statusEl.textContent = 'Terkonfirmasi';
    overlay.classList.add('is-locked');
  } else if (vote.avgDistance >= STRICT_DISTANCE * 0.85 || vote.distanceSpread > MAX_DISTANCE_SPREAD * 0.85) {
    statusEl.textContent = 'Belum stabil';
    overlay.classList.add('is-warning');
  } else if (vote.challengerCount > 0 && (vote.count - vote.challengerCount) < 2) {
    statusEl.textContent = 'Kandidat ambigu';
    overlay.classList.add('is-danger');
  } else {
    statusEl.textContent = 'Mengumpulkan';
  }
}
```

**j) Panggil `updateVotingCircle(vote)` di dua tempat di `captureAndScan`:**
- Setelah `updateVotingStatus(vote)` di line 738
- Setelah `updateVotingStatus(vote)` di line 693 (kasus "tidak dikenali")

**k) Sembunyikan lingkaran setelah lock fired (`lockStudentMatch`):**

```js
function lockStudentMatch(studentId) {
  stopScanningLoop();
  lastVotedStudentId = studentId;
  // Tampilkan state locked sebentar lalu hide saat form pelanggaran muncul
  const overlay = document.getElementById('voting-circle');
  if (overlay && !overlay.classList.contains('d-none')) {
    overlay.classList.add('is-locked');
    setTimeout(() => hideVotingCircle(), 1500);
  }
  // ... existing logic tetap
}
```

### 2. [c:/laragon/www/Fr-lbph/run.bat](c:/laragon/www/Fr-lbph/run.bat) — Supervisor Wrapper

Ganti file existing dengan wrapper yang:
- Loop: jika python exit (error atau manual Ctrl+C), restart otomatis
- Log ke `logs/service.log` dengan timestamp
- Backoff: jika restart 3x dalam 60 detik, berhenti (indikasikan crash loop)
- Banner startup yang jelas

```bat
@echo off
setlocal EnableDelayedExpansion
chcp 65001 >nul

REM ============================================================
REM Face Recognition LBPH — Supervisor Wrapper
REM Auto-restart service jika crash, dengan logging dan backoff.
REM ============================================================

set "PROJECT_DIR=%~dp0"
set "VENV_PY=%PROJECT_DIR%venv\Scripts\python.exe"
set "LOG_DIR=%PROJECT_DIR%logs"
set "LOG_FILE=%LOG_DIR%\service.log"
set "MAX_RESTARTS=3"
set "BACKOFF_WINDOW_SEC=60"

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

:MMAIN
set "RESTART_COUNT=0"
set "WINDOW_START=%time:~0,2%%time:~3,2%%time:~6,2%"

:LOOP
set "START_TS=%date% %time%"
echo [%START_TS%] === Memulai FR-LBPH service... ===  >> "%LOG_FILE%"

call "%VENV_PY%" "%PROJECT_DIR%app.py"  >> "%LOG_FILE%" 2>&1
set "EXIT_CODE=%errorlevel%"

set "END_TS=%date% %time%"
echo [%END_TS%] === Service exit (code=%EXIT_CODE%) === >> "%LOG_FILE%"

set "NOW_HMS=%time:~0,2%%time:~3,2%%time:~6,2%"
call :DIFFSEC WINDOW_START NOW_HMS DIFF
if %DIFF% gtr %BACKOFF_WINDOW_SEC% (
    set "RESTART_COUNT=0"
    set "WINDOW_START=%NOW_HMS%"
)

set /a "RESTART_COUNT+=1"
if %RESTART_COUNT% geq %MAX_RESTARTS% (
    echo [%date% %time%] !!! Restart %RESTART_COUNT%x dalam %BACKOFF_WINDOW_SEC%s. Berhenti. !!! >> "%LOG_FILE%"
    echo.
    echo ============================================================
    echo   SERVICE GAGAL RESTART %MAX_RESTARTS%x DALAM %BACKOFF_WINDOW_SEC% DETIK
    echo   Cek log: %LOG_FILE%
    echo ============================================================
    pause
    exit /b 1
)

echo [%date% %time%] Restart #%RESTART_COUNT% dalam 3 detik... >> "%LOG_FILE%"
timeout /t 3 /nobreak >nul
goto LOOP

:DIFFSEC
setlocal
set "A=000000%1" & set "A=!A:~-6!"
set "B=000000%2" & set "B=!B:~-6!"
set /a "A=1!A! - 1000000"
set /a "B=1!B! - 1000000"
set /a "DIFF=(B-A+86400) %% 86400"
endlocal & set "%3=%DIFF%"
goto :eof
```

## Critical Files Reference

| File | Perubahan |
|------|-----------|
| `resources/views/guru/attendance/index.blade.php` | Tambah SVG overlay, CSS, konstanta voting, time-decay + confirmation hold + circle driver |
| `c:/laragon/www/Fr-lbph/run.bat` | Ganti dengan supervisor wrapper |

## Implementation Steps (urutan eksekusi)

1. **Edit `index.blade.php`** — di urutan:
   a. Tambah CSS di `@push('custom-css')` (vars + selectors)
   b. Tambah SVG `<div id="voting-circle">` setelah element video
   c. Tambah JS: konstanta baru, state vars, modifikasi `evaluateFrameBuffer`
      (time-decay), modifikasi `captureAndScan` (confirmation hold), modifikasi
      `clearFrameBuffer` (reset state), modifikasi `lockStudentMatch` (hide circle),
      tambah `showVotingCircle` / `hideVotingCircle` / `updateVotingCircle`,
      wire `updateVotingCircle(vote)` di 2 tempat di `captureAndScan`.
2. **Ganti `c:/laragon/www/Fr-lbph/run.bat`** dengan supervisor wrapper.
3. **Smoke test manual**:
   - Buka `http://localhost/pelanggaran-siswa/guru/face-recognition`
   - Scan wajah yang terdaftar → lingkaran muncul, isi 0→6, hijau, lock setelah hold
   - Scan wajah blur/gelap → lingkaran kuning/merah, lock ditolak
   - Arahkan kamera ke siswa berbeda → reset, voting ulang
4. **Test supervisor**: `taskkill /F /IM python.exe`, lalu lihat auto-restart di log.
5. **Pipeline test tidak rusak**: file `tests/Feature/FaceRecognitionPipelineV2Test.php`
   **TIDAK disentuh** — kontrak Python tidak berubah.

## Risk & Mitigasi

| Risk | Mitigation |
|------|------------|
| Inline JS edit salah → voting rusak | Edit minimal: tambah konstanta, satu filter time-decay, satu if-else confirmation. Existing functions `evaluateFrameBuffer`, `lockStudentMatch`, `captureAndScan` **di-extend** bukan di-rewrite. |
| Lingkaran overlay menutupi wajah siswa | `pointer-events: none` + `z-index: 5` di bawah tombol kontrol. Tetap bisa lihat wajah untuk verifikasi manual. |
| Time-decay 2500ms terlalu agresif untuk scan lambat (1500ms interval × N) | Frame rate = 1 scan / 1.5s. Buffer 9 frame = 13.5s window total. Decay 2.5s = max 2 frame aktif per kali. Kalau voting butuh 6 vote, perlu 6 consecutive scan cocok dalam 2.5s = 4 successful scans = 6s real time. Realistis. |
| Confirmation hold 350ms × 2 passes = ~700ms menambah latency lock | Acceptable — total lock latency dari wajah muncul: ~4-6s scan + 700ms confirmation = 5-7s. User experience lebih yakin daripada lock instan. |
| Supervisor batch arithmetic HHMMSS overflow tengah malam | Wrap diff dengan modulo 86400 (sudah ada di script) |
| `performance.now()` throttling di background tab | Frontend tab harus foreground — service worker `public/sw.js` sudah pause anyway. OK. |
| Log file `service.log` membengkak | Append-only, user truncate manual. Bisa ditambah scheduled task nanti. |

## Open Questions During Implementation

- **CSS specificity override Metronic theme** — kelas `.alert`, `.btn` Metronic
  punya specificity tinggi. CSS variable + scoped class `.voting-circle-overlay`
  memastikan tidak konflik. Akan dicek di awal.
- **Posisi SVG di HTML** — akan ditempatkan setelah `<video>` dan sebelum
  `scanner-laser` agar layering benar.