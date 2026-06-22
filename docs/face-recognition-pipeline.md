# Face Recognition Pipeline v2

Dokumentasi internal untuk pipeline face recognition yang dipakai di modul
guru attendance (URL: `/guru/attendance`, route name: `guru.attendance`).

Pipeline v2 menggantikan response contract v1. Perubahan utama:
- Field `confidence` (float 0-100, lebih besar lebih baik) dihapus.
- Field `distance` (float, lebih kecil lebih mirip) dan `match_strength`
  (float 0-1, lebih tinggi lebih mirip) menggantikan.
- Pipeline deteksi Haar + preprocessing CLAHE dipisah (lihat
  [Pipeline v2 details](#pipeline-v2-details) di bawah).

## Arsitektur

```
[Browser] --(Base64 image, 1.5s interval)--> [Laravel /guru/face-recognition/scan]
                                                          |
                                                          v
                                                  [FaceRecognitionService]
                                                          |
                                                          v HTTP POST /recognize
                                                  [Python Flask @ 127.0.0.1:5000]
                                                          |
                                                          v
                                                  [OpenCV + LBPH model]
                                                          |
                                                          v
                                                  [JSON response v2]
```

- **Browser**: kamera capture via `getUserMedia()`, kirim Base64 ke Laravel.
  View: `resources/views/guru/attendance/index.blade.php`.
- **Laravel controller**: `app/Http/Controllers/Guru/FaceRecognitionController.php`.
  Terima request, panggil `FaceRecognitionService::scanFace()`.
- **Laravel service**: `app/Services/FaceRecognitionService.php`. Jembatan HTTP
  ke Python Flask, normalisasi response Python ke top-level fields.
- **Python Flask**: `C:\laragon\www\FR_LPBH\app.py`. Terima image, deteksi
  wajah, multi-scale predict, return response contract v2.
- **OpenCV LBPH**: model yang sudah di-train di
  `FR_LPBH/trained_model/trained_model.xml`.

## Komponen

### Python service (`C:\laragon\www\FR_LPBH`)

- **Entry**: `app.py`
- **Host/Port**: `127.0.0.1:5000` (di-hardcode di `app.py:407`)
- **Endpoints**:
  - `GET /health` — status service + `pipeline_version: "2.0"`
  - `POST /train` — train model dari folder `dataset/`
  - `POST /recognize` — recognize wajah (lihat [Response Contract v2](#response-contract-v2))
- **Konstanta utama** (lihat `app.py`):
  - `STRICT_THRESHOLD = 60.0`
  - `LOOSE_THRESHOLD = 75.0`
  - `FACE_SIZE = (200, 200)`
  - `clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))`

### Laravel service (`C:\laragon\www\pelanggaran-siswa`)

- **File**: `app/Services/FaceRecognitionService.php`
- **Base URL**: dari `AppSetting::getValue('fr_lbph_base_url', 'http://127.0.0.1:5000')`
  (lihat migration `2026_06_19_050049_add_fr_lbph_base_url_to_app_settings.php`).
- **Methods**:
  - `scanFace($base64Image)` — panggil `/recognize` dengan cURL
    (CONNECTTIMEOUT 5s, TIMEOUT 25s, JSON body). Normalisasi `top_match.*`
    ke top-level `student_id`, `distance`, `match_strength` agar controller
    tetap baca `$res['student_id']` tanpa perubahan.
  - `fetchPipelineVersion()` — query `/health` (TIMEOUT 3s, CONNECTTIMEOUT 2s),
    return `pipeline_version` atau `null` (tidak throw jika service down).
  - `getPipelineVersion()` — return versi pipeline yang terakhir di-fetch.

### Controller (`C:\laragon\www\pelanggaran-siswa`)

- **File**: `app/Http/Controllers/Guru/FaceRecognitionController.php`
- **Methods**:
  - `index(FaceRecognitionService $frService)` — render
    `guru.attendance.index` dengan `pipelineVersion` dan daftar pelanggaran aktif.
  - `scan(Request, FaceRecognitionService)` — handle AJAX recognize dari browser.
    Validasi: `'image' => ['required', 'string']`. Jika `success` dan `recognized`,
    lookup `Siswa` by ID dan kembalikan data siswa lengkap ke frontend.
  - `storeFromFace(Request, WhatsAppService)` — simpan pelanggaran + kirim WA.

### View (`C:\laragon\www\pelanggaran-siswa`)

- **File**: `resources/views/guru/attendance/index.blade.php`
- UI kamera + display match strength + voting logic (lihat [Threshold & Voting](#threshold--voting)).

### Routes

Lihat `routes/web.php`:

```php
Route::get('/guru/attendance', [..., 'index'])->name('guru.attendance');
Route::post('/guru/face-recognition/scan', [..., 'scan'])->name('guru.face-recognition.scan');
Route::post('/guru/pelanggaran-siswa/store-from-face', [..., 'storeFromFace'])->name('guru.pelanggaran-siswa.store-from-face');
```

## Response Contract v2

### `POST /recognize` (Python Flask)

**Request body**:
```json
{ "image": "data:image/jpeg;base64,/9j/4AAQ..." }
```

Atau Base64 tanpa prefix `data:image/...;base64,` — keduanya diterima
(`app.py` strip prefix jika ada).

**Response jika wajah dikenali (match kuat)**:
```json
{
  "success": true,
  "recognized": true,
  "top_match": {
    "student_id": 100,
    "distance": 0.0662,
    "match_strength": 0.9993
  },
  "candidates": [
    {"student_id": 100, "distance": 0.0662, "match_strength": 0.9993}
  ],
  "match_level": "strict",
  "face_size": [200, 200],
  "message": "Recognized"
}
```

**Response jika wajah terdeteksi tapi tidak ada match (distance >= LOOSE_THRESHOLD)**:
```json
{
  "success": true,
  "recognized": false,
  "top_match": null,
  "candidates": [...],
  "match_level": "no_match",
  "face_size": [200, 200],
  "message": "Wajah tidak dikenali (low confidence)"
}
```

**Response jika tidak ada wajah terdeteksi**:
```json
{
  "success": true,
  "recognized": false,
  "candidates": [],
  "message": "Wajah tidak terdeteksi."
}
```

**Response jika model belum dilatih** (HTTP 400):
```json
{
  "success": false,
  "recognized": false,
  "message": "Model face recognition belum dilatih (trained_model.xml tidak ditemukan)."
}
```

### Field Reference

| Field | Tipe | Arti |
| --- | --- | --- |
| `success` | bool | Pipeline berjalan tanpa error (request valid, JSON valid, dsb). |
| `recognized` | bool | Ada wajah yang match (distance < LOOSE_THRESHOLD). |
| `top_match` | object\|null | Kandidat terbaik. `null` jika tidak ada match. |
| `top_match.student_id` | int | ID siswa (label LBPH). |
| `top_match.distance` | float | LBPH distance. Lebih kecil = lebih mirip. |
| `top_match.match_strength` | float in [0,1] | `max(0, 1 - distance/100)`. Lebih tinggi = lebih mirip. |
| `candidates` | array | Top 3 kandidat terurut ascending by distance. |
| `match_level` | str | `"strict"` (dist<60), `"loose"` (60<=dist<75), `"no_match"` (dist>=75 atau tidak ada). |
| `face_size` | array | Dimensi wajah yang digunakan untuk training `[200, 200]`. |
| `message` | str | Pesan untuk debugging/UI. |

### Normalisasi oleh Laravel service

`FaceRecognitionService::scanFace()` menormalisasi response Python dengan
menyalin `top_match.{student_id, distance, match_strength}` ke top-level
fields. Jadi response yang dilihat controller/view punya tambahan:

```json
{
  "top_match": { "student_id": 100, ... },
  "student_id": 100,
  "distance": 0.0662,
  "match_strength": 0.9993
}
```

Field `confidence` TIDAK ADA di response v2. Jangan pakai. Logic v1
`if (confidence > 50)` sudah dihapus; pakai `if (distance < 60)` atau
`if (match_strength > 0.5)`.

### `GET /health` (Python Flask)

```json
{
  "status": "active",
  "pipeline_version": "2.0",
  "model_loaded": true,
  "model_path": "trained_model/trained_model.xml",
  "students_count": 5,
  "threshold_strict": 60.0,
  "threshold_loose": 75.0,
  "preprocessing": "clahe",
  "face_size": [200, 200],
  "message": "Service Face Recognition LBPH is running."
}
```

## Threshold & Voting

### Backend threshold (Python)

- `STRICT_THRESHOLD = 60.0` — match kuat.
- `LOOSE_THRESHOLD = 75.0` — match lemah; tetap dikembalikan sebagai
  `top_match` tapi `match_level="loose"`.
- `>= 75.0` — `top_match` di-set ke `null` dan `match_level="no_match"`.

### Frontend voting (Browser)

View `guru/attendance/index.blade.php` mengimplementasikan multi-frame
voting (lihat konstanta di `DOMContentLoaded` handler):

| Konstanta | Nilai | Arti |
| --- | --- | --- |
| `FRAME_BUFFER_SIZE` | 5 | Frame yang dikumpulkan sebelum voting final. |
| `VOTE_MIN_WIN` | 3 | Minimum vote untuk locking. |
| `STRICT_DISTANCE` | 60.0 | Avg distance harus < ini untuk lock. |

Interval scan: `setInterval(captureAndScan, 1500)` (1.5 detik per frame).

Logikanya:
1. Capture frame dari video stream, convert ke Base64 JPEG.
2. POST ke `guru.face-recognition.scan`.
3. Push hasil ke `frameBuffer` (kapasitas 5, FIFO).
4. `evaluateFrameBuffer()` group by `student_id`, pilih yang `count` paling
   banyak. Lock hanya jika `count >= 3` AND `avgDistance < 60`.

## Pipeline v2 details

Perubahan utama dari v1 (lihat kode `app.py`):

1. **Detector parameter beda training vs predict** (`extract_face_for_recognition`):
   - `detector_strict=True` (training): `minNeighbors=5, minSize=60`. Menolak
     gambar yang kemungkinan non-wajah.
   - `detector_strict=False` (predict): `minNeighbors=3, minSize=40`. Lebih
     permisif untuk menangkap wajah di webcam.

2. **CLAHE pada face ROI saja, bukan full image** (`preprocess_face_roi`).
   Sebelumnya CLAHE diterapkan ke seluruh gambar sebelum Haar detection,
   yang menurunkan akurasi karena Haar dilatih untuk natural grayscale.
   Sekarang: deteksi Haar di raw grayscale → crop ROI → CLAHE pada ROI
   → resize ke `FACE_SIZE`.

3. **Multi-scale predict** (`recognize_unknown`). Predict pada 3 skala
   `(180,180)`, `(200,200)`, `(220,220)`, ambil distance minimum per
   student_id. Top 3 dikembalikan sebagai `candidates`.

4. **Filter label yang tidak ada di dataset** (`recognize_students_in_dataset`).
   Mencegah label dari model lama atau `-1` ikut lolos.

5. **Augmentasi training** (`/train`): horizontal flip per wajah. Sample
   efektif menjadi 2x jumlah wajah.

## Setup Local Development

### 1. Start Python service

```bash
cd C:\laragon\www\FR_LPBH
source venv/Scripts/activate  # Git Bash
# atau: venv\Scripts\activate (cmd)
python app.py
```

Atau double-click `run.bat`.

Expected: `Running on http://127.0.0.1:5000`

### 2. Verify pipeline

```bash
curl -s http://127.0.0.1:5000/health
```

Expected: `{"status": "active", "pipeline_version": "2.0", ...}`

### 3. Train model (jika belum)

Taruh foto di `FR_LPBH/dataset/<student_id>/*.jpg` lalu:

```bash
curl -X POST http://127.0.0.1:5000/train
```

Response berisi `stats.total_faces_extracted` dan
`stats.after_augmentation`. Idealnya `after_augmentation` = 2x jumlah wajah
(karena flip augmentation). Jika `total_faces_skipped` besar, cek dataset.

### 4. Start Laravel

```bash
cd C:\laragon\www\pelanggaran-siswa
php artisan serve
```

Expected: `Server running on http://127.0.0.1:8000`

### 5. (Opsional) Set base URL di `app_settings`

Default `fr_lbph_base_url = http://127.0.0.1:5000`. Jika Python berjalan
di host/port lain:

```sql
UPDATE app_settings SET value = 'http://192.168.1.10:5000' WHERE `key` = 'fr_lbph_base_url';
```

### 6. Test pipeline via UI

Buka `http://127.0.0.1:8000/guru/attendance`, login, aktifkan kamera, arahkan
ke wajah siswa yang sudah ada di dataset.

## Testing

### Python test (5 test)

```bash
cd C:\laragon\www\FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

5 test PASS:
- `test_health` — endpoint /health return valid + `pipeline_version`.
- `test_train_dummy` — train model dengan dummy dataset.
- `test_recognize_known` — recognize wajah yang ada di training.
- `test_recognize_empty` — handle gambar tanpa wajah.
- `test_response_shape` — validasi kontrak response v2 (top_match,
  distance, match_strength, tanpa `confidence`).

### Laravel test (pipeline v2)

```bash
cd C:\laragon\www\pelanggaran-siswa
php artisan test --filter FaceRecognition
```

Test ada di `tests/Feature/FaceRecognitionPipelineV2Test.php`. Strategi:
spawn PHP built-in HTTP server di port 5099 sebagai fake Python service,
mock `AppSetting::getValue` via Mockery agar service mengarah ke fake
server. Tidak bergantung pada Python service nyata atau driver PDO sqlite.

## Migration dari v1

Jika masih ada kode yang baca field `confidence` (v1):

- **Hapus** seluruh referensi `confidence` (field sudah tidak ada di response).
- Logic v1 `if (confidence > 50)` → `if (matchStrength > 0.5)` ATAU
  `if (distance < 60)`.
- Logic v1 `if (confidence > 75)` → `if (distance < 40)` ATAU
  `if (matchStrength > 0.6)`.
- Hapus juga referensi ke endpoint lama yang return `{recognized, confidence}`
  flat (sekarang ada di dalam `top_match`).

Lihat juga `FR_LPBH/README.md` (Python service) untuk catatan tuning
akurasi dan history pipeline.

## Troubleshooting

### Service tidak bisa connect

- Cek Python hidup: `curl http://127.0.0.1:5000/health`
- Cek port 5000 tidak di-block: `netstat -an | findstr 5000` (cmd)
- Cek base URL di `app_settings`:
  `SELECT * FROM app_settings WHERE \`key\`='fr_lbph_base_url';`

### `match_strength` selalu 0 atau `distance` selalu besar

- Cek training: `FR_LPBH/dataset/<student_id>/` harus berisi image wajah
  dengan format `.jpg`/`.jpeg`/`.png`.
- Cek model: `FR_LPBH/trained_model/trained_model.xml` ada dan
  `model_loaded=true` di `/health`.
- Cek response Python manual: `curl /recognize` dengan sample image
  dari dataset.

### `pipeline_version` null di view

`fetchPipelineVersion()` return null jika Python service down atau response
tidak valid. Ini by design — Laravel handle gracefully tanpa crash.
View `guru/attendance/index.blade.php` menerima `pipelineVersion` sebagai
nullable (saat ini tidak ditampilkan di UI, hanya tersedia untuk monitoring).

### Voting lock tidak pernah terjadi

- Cek `frameBuffer` tidak terus-menerus ter-reset (lihat logika
  `clearFrameBuffer()` di view: dipanggil saat "wajah tidak terdeteksi").
- Cek `STRICT_DISTANCE` di view konsisten dengan `STRICT_THRESHOLD` di Python
  (keduanya 60.0 saat ini).
- Cek pencahayaan webcam — wajah harus jelas untuk face detection.

### `Model belum dilatih` (HTTP 400)

Taruh dataset di `FR_LPBH/dataset/<id>/*.jpg` lalu `POST /train`.
Lihat `/health` → `model_loaded: true` setelah training sukses.

## File referensi

- `C:\laragon\www\FR_LPBH\app.py` — Python Flask service.
- `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py` — Python test.
- `C:\laragon\www\FR_LPBH\README.md` — Setup & tuning notes (Python side).
- `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php` — HTTP client.
- `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php` — Controller.
- `C:\laragon\www\pelanggaran-siswa\resources\views\guru\attendance\index.blade.php` — UI & voting.
- `C:\laragon\www\pelanggaran-siswa\tests\Feature\FaceRecognitionPipelineV2Test.php` — Laravel test.
- `C:\laragon\www\pelanggaran-siswa\routes\web.php` — Routes (baris 84-86).
- `C:\laragon\www\pelanggaran-siswa\database\migrations\2026_06_19_050049_add_fr_lbph_base_url_to_app_settings.php` — Setting migration.
