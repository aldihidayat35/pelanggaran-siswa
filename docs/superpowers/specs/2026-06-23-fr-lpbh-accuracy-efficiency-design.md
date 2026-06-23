# Desain: Penyempurnaan Akurasi & Efisiensi Pipeline FR_LPBH + Frontend `kamera-pelanggaran`

**Tanggal:** 2026-06-23
**Status:** Disetujui (menunggu implementasi)
**Lingkup:** `C:\laragon\www\FR_LPBH` (Python service) + `C:\laragon\www\pelanggaran-siswa` (Laravel + Blade frontend)
**Referensi:** `docs/superpowers/specs/2026-06-16-pelanggaran-siswa-design.md` (desain aplikasi pelanggaran-siswa yang sudah ada sebelumnya)

---

## 1. Latar Belakang & Tujuan

### 1.1 Latar Belakang

Pipeline face recognition untuk fitur kamera pelanggaran saat ini memiliki **7 masalah konkret** yang menurunkan akurasi identifikasi dan efisiensi request, terdistribusi di tiga lokasi:

- **Python service** (`C:\laragon\www\FR_LPBH\app.py`): preprocessing terbalik (CLAHE pada deteksi), augmentasi `predict()` yang merusak konsistensi label, parameter Haar longgar.
- **Laravel service & controller** (`app/Services/FaceRecognitionService.php`, `app/Http/Controllers/Guru/FaceRecognitionController.php`): nama field menyesatkan (confidence vs distance), tidak ada retry/health cache, query DB per frame.
- **Frontend Blade** (`resources/views/guru/kamera-pelanggaran/index.blade.php`): parsing response pakai nama field salah, tidak ada indikator voting, tidak ada guard re-lock.

### 1.2 Tujuan

1. **Akurasi:** Menurunkan false positive dan false negative saat pengenalan wajah di kondisi cahaya教室 tidak merata.
2. **Konsistensi:** Top-N candidates dari Python service menjadi benar-benar **top-N untuk 1 student_id** (bukan label acak dari transformasi yang merusak).
3. **Efisiensi:** Mengurangi query DB per sesi scan dari ~15 menjadi ~3.
4. **Kejelasan kontrak data:** Field response punya nama yang sesuai konsep (`distance` adalah distance LBPH, `match_strength` adalah skor keyakinan 0–1).
5. **Verifikasi:** Skrip uji otomatis yang bisa dijalankan ulang untuk membuktikan setiap endpoint menghasilkan response sesuai spec.

### 1.3 Non-Tujuan (YAGNI)

- Mengganti LBPH ke deep learning (FaceNet/ArcFace) — perubahan arsitektur besar, di luar lingkup.
- Refactor `FaceRecognitionController` ke service pattern — perubahan di-place, refactor terpisah.
- Menambah WebSocket — polling 1.5 detik per kamera cukup untuk use case.
- Mengubah skema database — tidak ada kebutuhan.
- UI redesign — hanya tambah indikator voting, layout tidak berubah.

---

## 2. Arsitektur & Data Flow

### 2.1 Diagram Alur Saat Ini

```
[Webcam] -> [Browser: index.blade.php]
                |  every 1.5s
                v
        [Laravel: /guru/face-recognition/scan]
                |
                v
        [FaceRecognitionService::scanFace]
                |  HTTP POST
                v
        [Python Flask: /recognize]
                |
                v
        [LBPH recognizer] -> response
                ^
                |
        [FaceRecognitionController::scan]
                |  query Siswa::find()
                v
        [Database: tabel siswa]
                |
                v
        [Browser: lockStudentMatch -> populate UI]
```

### 2.2 Diagram Alur Sesudah

```
[Webcam] -> [Browser: index.blade.php]
                |  every 1.5s (5-frame voting buffer)
                v
        [Laravel: /guru/face-recognition/scan]
                |
                v
        [FaceRecognitionService::scanFace]
                |  - read health cache (30s TTL)
                |  - retry 1x on timeout
                |  - normalize response shape
                |  HTTP POST
                v
        [Python Flask: /recognize]
                |
                |  Step 1: detect face on RAW grayscale (Haar natural)
                |  Step 2: crop + apply CLAHE on ROI
                |  Step 3: multi-scale predict (200,180,220) -> 3 distances
                |  Step 4: top_match = label with avg(3 distances)
                |  Step 5: match_strength = max(0, 1 - avg/100)
                v
        [Response: {top_match:{student_id, distance, match_strength}, candidates:[]}]
                ^
                |
        [FaceRecognitionService: normalize field names]
                |
                v
        [FaceRecognitionController::scan]
                |  Siswa::find() dengan cache in-memory
                v
        [Database: tabel siswa]
                |
                v
        [Browser: voting UI (progress bar) -> lockStudentMatch]
```

### 2.3 Perubahan Kontrak Response

**Sebelum (Python service `top_match`):**
```json
{ "student_id": 1, "confidence": 54.2 }
```
`confidence` sebenarnya adalah `distance` LBPH — nama menyesatkan karena **lower distance = better match**.

**Sesudah:**
```json
{
  "top_match": {
    "student_id": 1,
    "distance": 54.2,
    "match_strength": 0.458
  }
}
```

`distance` = nilai LBPH mentah (lower = better).
`match_strength` = skor 0–1 yang diturunkan: `max(0.0, 1.0 - distance/100)`. Hanya untuk display ke user, **bukan** untuk logika voting.

Logika voting frontend tetap pakai `distance < 60` karena lebih presisi.

---

## 3. Perubahan Detail Per Komponen

### 3.1 `C:\laragon\www\FR_LPBH\app.py`

#### 3.1.1 Preprocessing — pisahkan deteksi & recognition

**Fungsi diubah:** `detect_face_crop(gray_img)`, `preprocess_frame(gray_img)`.

**Aturan baru:**
- Deteksi wajah di gambar **mentah grayscale** (sebelum CLAHE). Haar cascade dilatih untuk gambar natural.
- CLAHE diterapkan **hanya pada face ROI** setelah crop, sebelum resize.

**Kode baru (referensi):**
```python
def preprocess_face_roi(face_roi):
    """Apply CLAHE to cropped face ROI only."""
    return clahe.apply(face_roi)


def detect_face_raw(gray_img):
    """Detect face on RAW grayscale (no CLAHE). Returns (x,y,w,h) atau None."""
    faces = face_cascade.detectMultiScale(
        gray_img,
        scaleFactor=1.1,
        minNeighbors=4,
        minSize=(40, 40),
    )
    if len(faces) == 0:
        return None
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    return tuple(faces[0].tolist())


def extract_face_for_recognition(gray_img):
    """Full pipeline: detect on raw, crop, CLAHE on ROI, resize."""
    det = detect_face_raw(gray_img)
    if det is None:
        return None
    x, y, w, h = det
    roi = gray_img[y:y+h, x:x+w]
    roi_eq = preprocess_face_roi(roi)
    return cv2.resize(roi_eq, FACE_SIZE)
```

#### 3.1.2 Detector parameters — beda training vs predict

**Fungsi `train()`:** detector lebih ketat.
```python
faces = face_cascade.detectMultiScale(
    img,
    scaleFactor=1.1,
    minNeighbors=5,
    minSize=(60, 60),
)
```

**Fungsi `recognize()`:** detector lebih permisif.
```python
faces = face_cascade.detectMultiScale(
    gray,
    scaleFactor=1.1,
    minNeighbors=3,
    minSize=(40, 40),
)
```

#### 3.1.3 Multi-scale predict (menggantikan flip/rotasi)

**Fungsi `recognize()`** — ganti blok transformasi flip/rotasi dengan multi-scale:

```python
scales = [FACE_SIZE, (180, 180), (220, 220)]
distances = []
for s in scales:
    try:
        if s != FACE_SIZE:
            face_scaled = cv2.resize(face_resized, s)
        else:
            face_scaled = face_resized
        l, d = recognizer.predict(face_scaled)
        distances.append((int(l), float(d)))
    except Exception:
        continue

# Top_match = label dengan rata-rata distance terendah
if not distances:
    return jsonify({"success": True, "recognized": False, ...})

from collections import defaultdict
groups = defaultdict(list)
for label, dist in distances:
    groups[label].append(dist)

ranked = sorted(
    ((label, sum(ds)/len(ds), ds) for label, ds in groups.items()),
    key=lambda x: x[1]
)
top_label, top_avg_dist, _ = ranked[0]
match_strength = max(0.0, 1.0 - top_avg_dist / 100.0)

# Candidates: ambil 3 label dengan avg distance terkecil
candidates = [
    {"student_id": int(l), "distance": float(avg_d)}
    for l, avg_d, _ in ranked[:3]
]
```

**Alasan:** multi-scale lebih aman dari flip/rotasi karena LBPH robust terhadap perubahan skala kecil dan **mempertahankan label yang sama** di semua iterasi. Top-3 candidates sekarang benar-benar 3 student_id berbeda (atau label yang sama jika dataset kecil).

#### 3.1.4 Response shape `top_match`

Tambah `match_strength`:
```python
return jsonify({
    ...
    "top_match": {
        "student_id": top_label,
        "distance": float(top_avg_dist),
        "match_strength": float(match_strength),
    },
    "candidates": candidates,
    ...
})
```

#### 3.1.5 Endpoint `/recognize_batch` (efisiensi)

Menerima list frame:
```python
@app.route('/recognize_batch', methods=['POST'])
def recognize_batch():
    data = request.get_json()
    if not data or 'images' not in data or not isinstance(data['images'], list):
        return jsonify({"success": False, "message": "Field 'images' (list) wajib ada."}), 400

    results = []
    for img_b64 in data['images']:
        # Jalankan ulang logika recognize() per frame
        # (Refactor: ekstrak logika ke helper _recognize_one(b64))
        result = _recognize_one(img_b64)
        results.append(result)

    # Aggregate: cari label paling sering dengan avg distance terendah
    # (logika voting server-side, opsional untuk dipakai frontend)
    return jsonify({"success": True, "frames": results, "count": len(results)})
```

**Catatan:** endpoint ini **opsional** untuk adopsi frontend. Frontend boleh tetap pakai `/recognize` 5x dengan voting client-side. Batch endpoint disediakan untuk eksperimen & optimasi masa depan.

#### 3.1.6 `/health` — tambah info tambahan

Tidak diubah kecuali tambah field:
```python
"pipeline_version": "2.0",
"detector_strategy": "raw-grayscale",
"predict_strategy": "multi-scale",
"multi_scale_sizes": [200, 180, 220],
```

### 3.2 `C:\laragon\www\FR_LPBH\README.md`

Tambah section "Pipeline v2" yang menjelaskan:
- Deteksi di raw grayscale, CLAHE hanya di ROI.
- Multi-scale predict menggantikan flip/rotasi.
- Response shape baru (`distance` + `match_strength`).
- Dataset yang dilatih dengan pipeline v1 **harus di-retrain** karena preprocessing berubah.

### 3.3 `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`

#### 3.3.1 Field naming — `distance` + `match_strength`

**Ganti:**
```php
// LAMA (baris 83-86)
if (isset($result['top_match']) && is_array($result['top_match'])) {
    $result['student_id'] = $result['top_match']['student_id'] ?? null;
    $result['confidence'] = $result['top_match']['confidence'] ?? null;
}
```

**Menjadi:**
```php
if (isset($result['top_match']) && is_array($result['top_match'])) {
    $result['student_id']    = $result['top_match']['student_id']    ?? null;
    $result['distance']      = $result['top_match']['distance']      ?? null;
    $result['match_strength'] = $result['top_match']['match_strength'] ?? null;
}
```

`student_id` tetap di-normalisasi ke top-level untuk kompatibilitas dengan `FaceRecognitionController::scan` yang baca `$res['student_id']`.

#### 3.3.2 Retry & health cache

Tambah konstanta class:
```php
private const CONNECT_TIMEOUT = 5;
private const REQUEST_TIMEOUT = 25;
private const MAX_RETRIES = 1;
private const RETRY_BACKOFF_MS = 500;
private const HEALTH_CACHE_TTL = 30; // detik
```

Logika retry (1x untuk network failure, tidak untuk HTTP error):
```php
$attempt = 0;
$response = null;
$error = null;
$httpCode = 0;

while ($attempt <= self::MAX_RETRIES) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [...]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (!$error && $httpCode === 200) break;

    $attempt++;
    if ($attempt <= self::MAX_RETRIES) {
        usleep(self::RETRY_BACKOFF_MS * 1000);
    }
}
```

**Health cache** (di-skip dalam spec ini karena out-of-scope untuk spec sederhana; cukup retry saja. Health cache bisa ditambahkan di iterasi berikut jika dibutuhkan — tidak menghalangi akurasi).

*Catatan revisi:* health cache TIDAK diimplementasikan di iterasi ini untuk menjaga scope tetap kecil. Retry saja sudah cukup untuk "ketahanan terhadap service restart" yang dicanangkan.

#### 3.3.3 Kembalikan full `top_match` (jangan flatten)

Selain normalisasi `student_id`/`distance`/`match_strength` ke top-level, pertahankan `top_match` utuh agar frontend bisa baca `top_match.match_strength` langsung:

```php
// Biarkan $result['top_match'] tetap utuh
// Frontend bisa akses res.top_match.distance ATAU res.distance
```

### 3.4 `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php`

#### 3.4.1 Cache siswa in-memory

Tambah property static:
```php
private static array $siswaCache = [];
private const SISWA_CACHE_TTL = 300; // 5 menit
```

Ganti `Siswa::find($studentId)`:
```php
$cacheKey = "siswa_{$studentId}";
$now = time();

if (isset(self::$siswaCache[$cacheKey])) {
    $entry = self::$siswaCache[$cacheKey];
    if ($now - $entry['ts'] < self::SISWA_CACHE_TTL) {
        $siswa = $entry['data'];
    } else {
        unset(self::$siswaCache[$cacheKey]);
        $siswa = null;
    }
}

if (!isset($siswa)) {
    $siswa = Siswa::find($studentId);
    if ($siswa) {
        self::$siswaCache[$cacheKey] = ['data' => $siswa, 'ts' => $now];
    }
}
```

#### 3.4.2 Normalisasi field di response siswa (tidak berubah)

Bagian ini sudah benar dan tetap dipakai. Tidak ada perubahan.

### 3.5 `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php`

#### 3.5.1 Parsing response yang benar

**Ganti (baris 518-519):**
```javascript
// LAMA
const studentId = (res.top_match && res.top_match.student_id) ?? res.student_id ?? null;
const distance = (res.top_match && res.top_match.confidence) ?? res.confidence ?? 999;
```

**Menjadi:**
```javascript
const studentId = (res.top_match && res.top_match.student_id) ?? res.student_id ?? null;
const distance  = (res.top_match && res.top_match.distance)   ?? res.distance   ?? 999;
const matchStrength = (res.top_match && res.top_match.match_strength) ?? res.match_strength ?? 0;
```

#### 3.5.2 Tampilkan progress voting visual

Tambah elemen UI di Blade (di dalam `scanner_status_alert` atau tempat lain yang sesuai):
```html
<div id="voting_progress" class="d-none mt-3">
    <div class="d-flex justify-content-between fs-8 mb-1">
        <span>Konfirmasi multi-frame</span>
        <span id="voting_counter">0/5</span>
    </div>
    <div class="progress h-6px">
        <div id="voting_bar" class="progress-bar bg-primary" style="width: 0%"></div>
    </div>
</div>
```

Tambah JS untuk update progress:
```javascript
function updateVotingUI(vote) {
    const progressEl = document.getElementById('voting_progress');
    const counterEl = document.getElementById('voting_counter');
    const barEl = document.getElementById('voting_bar');
    if (!vote) {
        progressEl.classList.add('d-none');
        return;
    }
    progressEl.classList.remove('d-none');
    const pct = (vote.count / FRAME_BUFFER_SIZE) * 100;
    counterEl.innerText = `${vote.count}/${FRAME_BUFFER_SIZE} frame cocok (avg ${vote.avgDistance.toFixed(1)})`;
    barEl.style.width = `${pct}%`;
    barEl.className = vote.readyToLock
        ? 'progress-bar bg-success'
        : 'progress-bar bg-primary';
}
```

Panggil `updateVotingUI(vote)` di dalam `captureAndScan` setelah `evaluateFrameBuffer()`.

#### 3.5.3 Guard — jangan scan jika sudah locked

**Ganti pemanggilan `captureAndScan`** di `startScanningLoop`/`stopScanningLoop`:
```javascript
function captureAndScan() {
    // Guard: hentikan scan jika sudah lock
    if (lastVotedStudentId != null) {
        return;
    }
    if (!isScanningActive || !stream) return;
    // ... sisanya sama
}
```

#### 3.5.4 Tampilkan `match_strength` setelah lock

Di `populateStudentUI`, tambah:
```javascript
const strengthPct = Math.round((siswa.match_strength ?? 0) * 100);
document.getElementById('match_strength_text').innerText = `Keyakinan: ${strengthPct}%`;
```

Tambah elemen di card hasil (sisipkan setelah `student_points`):
```html
<div class="text-muted fs-8 mt-1" id="match_strength_text">Keyakinan: -</div>
```

---

## 4. Skrip Uji Otomatis

### 4.1 `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

**Dependensi (`scripts/requirements-test.txt`):**
```
Pillow>=10.0.0
requests>=2.31.0
```

**Test cases:**

1. **test_health**: GET `/health`. Assert `status==active`, `pipeline_version=="2.0"`, `students_count>=0`, `model_loaded` adalah bool.
2. **test_train_dummy**: Buat dataset dummy — 3 siswa (id 100, 101, 102), masing-masing 4 foto wajah sintetik (oval hitam di kanvas 200×200 putih). POST `/train`. Assert `success==true`, `unique_students==3`, `after_augmentation==24` (4 foto × 2 augmentasi × 3 siswa).
3. **test_recognize_known**: Kirim gambar yang sama dengan dataset siswa 100. Assert `recognized==true`, `top_match.student_id==100`, `top_match.distance < 60`, `0 <= top_match.match_strength <= 1`.
4. **test_recognize_empty**: Kirim gambar putih polos 200×200. Assert `recognized==false`.
5. **test_recognize_response_shape**: Assert `top_match` memiliki `student_id`, `distance`, `match_strength`. Assert `candidates` adalah list, max 3 item, masing-masing punya `student_id` dan `distance`.

**Cleanup:** hapus folder dataset dummy `dataset/100/`, `dataset/101/`, `dataset/102/` setelah test.

### 4.2 `C:\laragon\www\pelanggaran-siswa\scripts\test_face_scan_endpoint.php`

**Test cases:**

1. **test_scan_no_image**: POST tanpa field `image`. Assert HTTP 422, validation error.
2. **test_scan_empty_image**: POST dengan base64 string kosong. Assert `success==false` (atau HTTP 422).
3. **test_scan_response_shape_with_dummy_data**: POST dengan base64 gambar dummy 1×1 pixel. Assert response mengandung `success`, `recognized`. Jika `recognized==true`, pastikan `siswa` ada. Jika `recognized==false`, pastikan `message` ada.

**Asumsi:** server Laravel & Python service keduanya berjalan saat test dijalankan. Test gagal (skip) dengan pesan jelas jika tidak bisa konek.

---

## 5. Strategi Eksekusi & Rollback

### 5.1 Urutan Eksekusi

1. Ubah `FR_LPBH/app.py` (semua perubahan Python).
2. Jalankan `scripts/test_fr_lbph.py` — pastikan 5 test case pass.
3. Ubah `FaceRecognitionService.php`.
4. Ubah `FaceRecognitionController.php`.
5. Ubah `kamera-pelanggaran/index.blade.php`.
6. Jalankan `scripts/test_face_scan_endpoint.php` — pastikan 3 test case pass.
7. Update `FR_LPBH/README.md` dengan dokumentasi pipeline v2.

### 5.2 Rollback

- Setiap perubahan disimpan sebagai edit file biasa, bukan migrasi. Untuk rollback: revert dengan `git checkout <file>` (jika repo di-init) atau backup manual.
- Dataset lama yang dilatih dengan pipeline v1 **harus di-retrain** setelah perubahan. Spec ini tidak menghapus dataset; admin tinggal jalankan `train.bat` ulang.

### 5.3 Breaking Changes yang Dikomunikasikan ke User

- Field `top_match.confidence` hilang, diganti `top_match.distance` dan `top_match.match_strength`.
- Frontend yang consume response ini **harus update** (sudah di-cover di spec).
- Nilai `confidence` di response yang dinormalisasi Laravel service juga ikut hilang; ganti ke `distance` dan `match_strength`.

---

## 6. Berkas yang Diubah & Ditambah

### Diubah (5)
1. `C:\laragon\www\FR_LPBH\app.py`
2. `C:\laragon\www\FR_LPBH\README.md`
3. `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`
4. `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php`
5. `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php`

### Ditambah (3)
6. `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`
7. `C:\laragon\www\FR_LPBH\scripts\requirements-test.txt`
8. `C:\laragon\www\pelanggaran-siswa\scripts\test_face_scan_endpoint.php`

---

## 7. Spesifikasi Lolos Self-Review

### Placeholder scan
✅ Tidak ada "TBD"/"TODO" dalam spec. Health cache secara eksplisit di-catat sebagai **tidak diimplementasikan** di iterasi ini (bukan placeholder, keputusan sadar).

### Konsistensi internal
✅ Nama field `distance` dan `match_strength` konsisten di semua bagian (Python, Laravel service, controller, frontend).
✅ Logika voting `avgDistance < 60` konsisten dengan threshold `STRICT_THRESHOLD = 60.0` di Python.
✅ Detektor parameter berbeda training vs predict — dijelaskan di section 3.1.2 dan dikodekan di section implementasi.

### Scope check
✅ Spec ini fokus pada 1 pipeline terpadu (FR_LPBH + kamera-pelanggaran). Tidak menyentuh modul lain di pelanggaran-siswa.

### Ambiguity check
✅ "Match strength" didefinisikan eksplisit sebagai `max(0, 1 - distance/100)`.
✅ Cache TTL didefinisikan eksplisit (5 menit untuk siswa, 30 detik untuk health — meski health cache tidak diimplementasi).
✅ "Frame voting visual" dijelaskan: progress bar dengan counter "3/5 frame cocok (avg 54.2)".
