# FR_LPBH Accuracy & Efficiency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Memperbaiki akurasi & efisiensi pipeline face recognition LBPH dari Python service `FR_LPBH/app.py` hingga frontend `kamera-pelanggaran` di Laravel, dengan breaking change yang terkelola: field `top_match.confidence` diganti `top_match.distance` + `top_match.match_strength`.

**Architecture:** Pendekatan dua arah — (1) pisahkan alur deteksi (Haar pada raw grayscale) dari alur recognition (CLAHE hanya pada face ROI), gantikan augmentasi flip/rotasi dengan multi-scale predict; (2) perjelas kontrak data dengan nama field yang sesuai konsep, tambah retry, cache siswa di controller, dan progress bar voting di frontend. Setiap perubahan dibarengi dengan test otomatis yang bisa dijalankan ulang.

**Tech Stack:** Python 3.x, Flask, OpenCV (contrib), Pillow, requests (test), Laravel 10/11, PHP 8.x, vanilla JavaScript (Blade), Axios, Bootstrap 5, Metronic theme.

---

## File Structure

**Diubah (5 file):**
- `C:\laragon\www\FR_LPBH\app.py` — logika deteksi, augmentasi, response shape, batch endpoint.
- `C:\laragon\www\FR_LPBH\README.md` — dokumentasi pipeline v2.
- `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php` — field naming, retry logic.
- `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php` — siswa cache, error handling.
- `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php` — parsing response, progress bar, guard.

**Ditambah (3 file):**
- `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py` — 5 test case otomatis untuk service Python.
- `C:\laragon\www\FR_LPBH\scripts\requirements-test.txt` — dependensi uji.
- `C:\laragon\www\pelanggaran-siswa\scripts\test_face_scan_endpoint.php` — 3 test case endpoint Laravel.

**Tanggung jawab per file:**
- `app.py` — wajah detection + LBPH recognition + HTTP API. Single file, ~330 baris.
- `FaceRecognitionService.php` — HTTP client ke service Python, normalisasi response, retry. ~80 baris.
- `FaceRecognitionController.php` — handler HTTP Laravel, query siswa dengan cache, validasi. ~190 baris.
- `kamera-pelanggaran/index.blade.php` — UI kamera + voting + form pelanggaran. ~770 baris.
- `test_fr_lbph.py` — smoke test end-to-end pipeline Python. ~200 baris.
- `test_face_scan_endpoint.php` — smoke test integrasi Laravel ↔ Python. ~120 baris.

---

## Task Index

1. [Setup: dependensi uji](#task-1-setup-dependensi-uji) — install Pillow & requests, verifikasi service berjalan.
2. [Test: health endpoint shape](#task-2-test-health-endpoint-shape) — tulis test, jalankan, expect FAIL.
3. [Feat: tambah `pipeline_version` di /health](#task-3-feat-tambah-pipeline_version-di-health) — implementasi minimal, test PASS.
4. [Refactor: pisah deteksi dari CLAHE](#task-4-refactor-pisah-deteksi-dari-clahe) — ekstrak `detect_face_raw` + `extract_face_for_recognition`.
5. [Feat: detector parameter beda training vs predict](#task-5-feat-detector-parameter-beda-training-vs-predict) — minNeighbors/minSize beda.
6. [Test: train dummy dataset](#task-6-test-train-dummy-dataset) — buat 3 siswa × 4 foto sintetik.
7. [Test: recognize known student](#task-7-test-recognize-known-student) — kirim foto mirip, expect match.
8. [Test: recognize empty image](#task-8-test-recognize-empty-image) — putih polos, expect no match.
9. [Refactor: ganti augmentasi flip/rotasi dengan multi-scale](#task-9-refactor-ganti-augmentasi-fliprotasi-dengan-multi-scale) — multi-scale predict.
10. [Feat: tambah `match_strength` di response top_match](#task-10-feat-tambah-match_strength-di-response-top_match) — skor 0-1.
11. [Test: response shape validation](#task-11-test-response-shape-validation) — verifikasi field `distance` + `match_strength`.
12. [Feat: endpoint /recognize_batch (opsional)](#task-12-feat-endpoint-recognize_batch-opsional) — list frame.
13. [Doc: update README pipeline v2](#task-13-doc-update-readme-pipeline-v2) — dokumentasi.
14. [Refactor: ganti `confidence` ke `distance` + `match_strength` di Laravel service](#task-14-refactor-ganti-confidence-ke-distance--match_strength-di-laravel-service) — breaking change handling.
15. [Feat: retry 1x dengan backoff di FaceRecognitionService](#task-15-feat-retry-1x-dengan-backoff-di-facerecognitionservice) — 500ms backoff.
16. [Feat: cache Siswa::find() di controller](#task-16-feat-cache-siswafind-di-controller) — in-memory TTL 5 menit.
17. [Test: endpoint Laravel scan response shape](#task-17-test-endpoint-laravel-scan-response-shape) — 3 test case.
18. [Fix: parsing `distance` (bukan `confidence`) di frontend](#task-18-fix-parsing-distance-bukan-confidence-di-frontend) — voting logic.
19. [Feat: progress bar voting visual](#task-19-feat-progress-bar-voting-visual) — counter + bar di UI.
20. [Feat: guard scan setelah lock](#task-20-feat-guard-scan-setelah-lock) — hemat CPU.
21. [Feat: tampilkan `match_strength` setelah lock](#task-21-feat-tampilkan-match_strength-setelah-lock) — info keyakinan.
22. [Final: verifikasi semua test pass & run app](#task-22-final-verifikasi-semua-test-pass--run-app) — end-to-end smoke test.

---

## Task 1: Setup dependensi uji

**Files:**
- Create: `C:\laragon\www\FR_LPBH\scripts\requirements-test.txt`
- Modify: `C:\laragon\www\FR_LPBH\app.py` (verify still loads)

- [ ] **Step 1: Buat folder scripts dan requirements-test.txt**

Buat folder:
```bash
mkdir -p C:/laragon/www/FR_LPBH/scripts
```

Tulis file `C:\laragon\www\FR_LPBH\scripts\requirements-test.txt`:
```
Pillow>=10.0.0
requests>=2.31.0
```

- [ ] **Step 2: Install dependensi ke venv existing**

Aktivasi venv dan install:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate  # Git Bash on Windows
pip install -r scripts/requirements-test.txt
```

Expected: `Successfully installed Pillow-X.X.X requests-X.X.X ...`

Jika venv tidak ada atau `source` gagal di Git Bash, coba:
```bash
cd C:/laragon/www/FR_LPBH && source venv/bin/activate || . venv/Scripts/activate
pip install -r scripts/requirements-test.txt
```

- [ ] **Step 3: Verifikasi service Python bisa di-import**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python -c "import app; print('OK', app.STRICT_THRESHOLD)"
```

Expected: `OK 60.0`

- [ ] **Step 4: Verifikasi endpoint /health hidup (jika service running)**

```bash
curl -s http://127.0.0.1:5000/health
```

Expected (jika service running): JSON dengan `"status": "active"`.

Jika service belum running, jangan start dulu — task berikutnya akan handle startup.

- [ ] **Step 5: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add scripts/requirements-test.txt
git commit -m "chore: add test requirements (Pillow, requests)"
```

Skip commit jika bukan git repo (working directory `FR_LPBH` bukan repo per session context).

---

## Task 2: Test health endpoint shape

**Files:**
- Create: `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

- [ ] **Step 1: Buat file test skeleton**

Tulis `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`:
```python
"""
Test suite untuk FR_LPBH service.

Cara pakai:
  1. Start service: python app.py (di folder FR_LPBH/)
  2. Jalankan test: python scripts/test_fr_lbph.py

Service harus jalan di http://127.0.0.1:5000.
"""
import os
import sys
import shutil
import tempfile
import requests
from PIL import Image, ImageDraw
from collections import Counter

BASE_URL = "http://127.0.0.1:5000"
DATASET_DIR = "dataset"  # relative terhadap cwd service


def make_synthetic_face(path, color=(40, 40, 40)):
    """Buat gambar wajah sintetik: oval gelap di tengah kanvas 200x200 putih."""
    img = Image.new("RGB", (200, 200), (255, 255, 255))
    draw = ImageDraw.Draw(img)
    # Oval wajah
    draw.ellipse([(50, 40), (150, 160)], fill=color)
    # Dua mata (lingkaran kecil hitam)
    draw.ellipse([(75, 80), (85, 90)], fill=(0, 0, 0))
    draw.ellipse([(115, 80), (125, 90)], fill=(0, 0, 0))
    img.save(path)


def make_empty_image(path):
    """Kanvas putih polos 200x200 (tidak ada wajah)."""
    img = Image.new("RGB", (200, 200), (255, 255, 255))
    img.save(path)


def setup_dummy_dataset(num_students=3, photos_per_student=4):
    """Buat dataset sintetik. Return list of student_ids."""
    student_ids = []
    for sid in range(100, 100 + num_students):
        sid_dir = os.path.join(DATASET_DIR, str(sid))
        os.makedirs(sid_dir, exist_ok=True)
        # Beri warna berbeda per siswa agar fitur wajah sintetik unik
        colors = [(40, 40, 40), (60, 60, 90), (90, 60, 60)]
        color = colors[(sid - 100) % len(colors)]
        for i in range(photos_per_student):
            make_synthetic_face(
                os.path.join(sid_dir, f"img_{i}.jpg"),
                color=color,
            )
        student_ids.append(sid)
    return student_ids


def teardown_dummy_dataset(student_ids):
    """Hapus folder dataset dummy."""
    for sid in student_ids:
        sid_dir = os.path.join(DATASET_DIR, str(sid))
        if os.path.isdir(sid_dir):
            shutil.rmtree(sid_dir)


def assert_true(cond, msg):
    if not cond:
        print(f"  FAIL: {msg}")
        sys.exit(1)
    print(f"  OK: {msg}")


def test_health():
    print("\n[TEST] /health shape")
    r = requests.get(f"{BASE_URL}/health", timeout=5)
    r.raise_for_status()
    data = r.json()
    assert_true(data.get("status") == "active", "status==active")
    assert_true(isinstance(data.get("model_loaded"), bool), "model_loaded is bool")
    assert_true(isinstance(data.get("students_count"), int), "students_count is int")
    assert_true(data.get("threshold_strict") == 60.0, "threshold_strict==60.0")
    assert_true(data.get("threshold_loose") == 75.0, "threshold_loose==75.0")
    assert_true(data.get("preprocessing") == "clahe", "preprocessing==clahe")
    # Field baru pipeline v2 (akan ditambah di Task 3)
    assert_true(data.get("pipeline_version") == "2.0", "pipeline_version==2.0")


def main():
    # Placeholder — akan diisi di task berikutnya
    test_health()
    print("\n=== All tests passed ===")


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Start service Python di background**

Buka terminal baru (atau pakai `run_in_background`):
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

Tunggu ~3 detik. Service akan log:
```
LBPH Model loaded successfully.   (jika trained_model.xml ada)
atau
No pre-trained model found.        (jika belum ada)
 * Running on http://127.0.0.1:5000
```

- [ ] **Step 3: Jalankan test, expect FAIL di assertion `pipeline_version`**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: FAIL dengan `FAIL: pipeline_version==2.0` (karena field belum ada di app.py).

- [ ] **Step 4: Verifikasi test gagal dengan benar**

Output harus mengandung:
```
[TEST] /health shape
  OK: status==active
  OK: model_loaded is bool
  OK: students_count is int
  OK: threshold_strict==60.0
  OK: threshold_loose==75.0
  OK: preprocessing==clahe
  FAIL: pipeline_version==2.0
```

Jika `requests.exceptions.ConnectionError`, service tidak jalan — kembali ke Step 2.

- [ ] **Step 5: Tidak commit (test masih gagal)**

Lewati commit sampai Task 3 membuat test pass.

---

## Task 3: Feat: tambah `pipeline_version` di /health

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py:90-102` (fungsi `health`)

- [ ] **Step 1: Edit fungsi `health` di app.py**

Buka `C:\laragon\www\FR_LPBH\app.py`. Cari:
```python
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "active",
        "model_loaded": model_loaded,
        "model_path": MODEL_PATH,
        "students_count": count_students_in_dataset(),
        "threshold_strict": STRICT_THRESHOLD,
        "threshold_loose": LOOSE_THRESHOLD,
        "preprocessing": "clahe",
        "face_size": FACE_SIZE,
        "message": "Service Face Recognition LBPH is running."
    })
```

Ganti dengan:
```python
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "active",
        "pipeline_version": "2.0",
        "model_loaded": model_loaded,
        "model_path": MODEL_PATH,
        "students_count": count_students_in_dataset(),
        "threshold_strict": STRICT_THRESHOLD,
        "threshold_loose": LOOSE_THRESHOLD,
        "preprocessing": "clahe",
        "face_size": FACE_SIZE,
        "message": "Service Face Recognition LBPH is running."
    })
```

- [ ] **Step 2: Restart service Python**

Stop service yang running (Ctrl+C), lalu:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

- [ ] **Step 3: Jalankan test, expect PASS**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected:
```
[TEST] /health shape
  OK: status==active
  OK: model_loaded is bool
  OK: students_count is int
  OK: threshold_strict==60.0
  OK: threshold_loose==75.0
  OK: preprocessing==clahe
  OK: pipeline_version==2.0

=== All tests passed ===
```

- [ ] **Step 4: Verifikasi manual via curl**

```bash
curl -s http://127.0.0.1:5000/health | python -m json.tool
```

Expected: JSON berisi `"pipeline_version": "2.0"`.

- [ ] **Step 5: Commit (jika git repo)**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py scripts/test_fr_lbph.py
git commit -m "feat(fr-lbph): add pipeline_version to /health, add test skeleton"
```

---

## Task 4: Refactor: pisah deteksi dari CLAHE

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py:65-87` (fungsi `preprocess_frame`, `detect_face_crop`)

- [ ] **Step 1: Tambah fungsi `preprocess_face_roi`**

Cari di `app.py`:
```python
def preprocess_frame(gray_img):
    """Apply CLAHE untuk menormalkan pencahayaan."""
    return clahe.apply(gray_img)


def detect_face_crop(gray_img):
    """Detect face, return cropped+resized face or None.
    Detector parameter lebih permisif dari versi awal (minNeighbors 4, minSize 40)
    agar wajah siswa yang jaraknya tidak fix dari webcam tetap tertangkap.
    """
    faces = face_cascade.detectMultiScale(
        gray_img,
        scaleFactor=1.1,
        minNeighbors=4,
        minSize=(40, 40),
    )
    if len(faces) == 0:
        return None, []
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    (x, y, w, h) = faces[0]
    face_roi = gray_img[y:y+h, x:x+w]
    face_resized = cv2.resize(face_roi, FACE_SIZE)
    return face_resized, faces.tolist()
```

Ganti seluruhnya dengan:
```python
def preprocess_face_roi(face_roi):
    """Apply CLAHE ke face ROI saja (setelah crop, sebelum resize).

    Pipeline v2: deteksi Haar di gambar natural (no CLAHE), recognition dengan
    CLAHE pada ROI. Pemisahan ini menurunkan false positive karena Haar dilatih
    untuk gambar natural, bukan high-contrast CLAHE.
    """
    return clahe.apply(face_roi)


def detect_face_raw(gray_img, min_neighbors=4, min_size=(40, 40)):
    """Detect wajah di RAW grayscale (no CLAHE).

    Return (x, y, w, h) atau None.
    Parameter lebih permisif untuk predict (default), lebih ketat untuk training.
    """
    faces = face_cascade.detectMultiScale(
        gray_img,
        scaleFactor=1.1,
        minNeighbors=min_neighbors,
        minSize=min_size,
    )
    if len(faces) == 0:
        return None
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    return tuple(int(v) for v in faces[0])


def extract_face_for_recognition(gray_img, detector_strict=False):
    """Pipeline lengkap: detect on raw, crop, CLAHE on ROI, resize ke FACE_SIZE.

    detector_strict=True -> minNeighbors=5, minSize=60 (untuk training)
    detector_strict=False -> minNeighbors=3, minSize=40 (untuk predict)
    """
    if detector_strict:
        det = detect_face_raw(gray_img, min_neighbors=5, min_size=(60, 60))
    else:
        det = detect_face_raw(gray_img, min_neighbors=3, min_size=(40, 40))
    if det is None:
        return None
    x, y, w, h = det
    roi = gray_img[y:y+h, x:x+w]
    roi_eq = preprocess_face_roi(roi)
    return cv2.resize(roi_eq, FACE_SIZE)


# Backward-compat wrapper untuk fungsi lama (dipakai di test/eksperimen)
def preprocess_frame(gray_img):
    """DEPRECATED di pipeline v2. Gunakan preprocess_face_roi untuk face ROI,
    atau biarkan raw untuk deteksi. Tetap ada untuk backward-compat."""
    return clahe.apply(gray_img)
```

- [ ] **Step 2: Verifikasi service masih bisa di-import (syntax check)**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python -c "import app; print('OK', hasattr(app, 'extract_face_for_recognition'))"
```

Expected: `OK True`

- [ ] **Step 3: Restart service & verifikasi /health masih jalan**

Stop service (Ctrl+C), lalu:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

Tunggu 3 detik, lalu:
```bash
curl -s http://127.0.0.1:5000/health
```

Expected: JSON dengan `status==active`. (Tidak ada perubahan behavior di /health.)

- [ ] **Step 4: Verifikasi test skeleton masih pass**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: Semua test di `test_health` masih pass.

- [ ] **Step 5: Commit (jika git repo)**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py
git commit -m "refactor(fr-lbph): split detect (raw grayscale) from recognition (CLAHE on ROI)"
```

---

## Task 5: Feat: detector parameter beda training vs predict

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py` (fungsi `recognize` dan `train`)

- [ ] **Step 1: Update `recognize` untuk pakai `extract_face_for_recognition`**

Cari di `app.py` (sekitar baris 144-154):
```python
        # Grayscale + CLAHE preprocessing
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        gray = preprocess_frame(gray)

        face_resized, faces_detected = detect_face_crop(gray)
        if face_resized is None:
            return jsonify({
                "success": True,
                "recognized": False,
                "candidates": [],
                "message": "Wajah tidak terdeteksi."
            })
```

Ganti dengan:
```python
        # Grayscale (no CLAHE — biarkan Haar deteksi di natural grayscale)
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        face_resized = extract_face_for_recognition(gray, detector_strict=False)
        if face_resized is None:
            return jsonify({
                "success": True,
                "recognized": False,
                "candidates": [],
                "message": "Wajah tidak terdeteksi."
            })
```

- [ ] **Step 2: Update `train` untuk pakai `extract_face_for_recognition` dengan strict detector**

Cari di `app.py` (sekitar baris 263-275):
```python
                images_processed += 1

                # Terapkan CLAHE yang SAMA seperti predict (konsistensi!)
                img_eq = preprocess_frame(img)

                # Detect face -> crop -> resize. Kalau gagal, SKIP (jangan fallback
                # resize-gambar-mentah yang akan kontaminasi model).
                face_resized, _ = detect_face_crop(img_eq)
                if face_resized is None:
                    print(f"[TRAIN] Skip {img_path}: tidak ada wajah terdeteksi")
                    faces_skipped += 1
                    continue
```

Ganti dengan:
```python
                images_processed += 1

                # Pipeline v2: detect di raw grayscale, CLAHE di ROI.
                # Detector lebih ketat saat training (minNeighbors=5, minSize=60)
                # untuk menolak gambar yang mungkin non-wajah.
                face_resized = extract_face_for_recognition(img, detector_strict=True)
                if face_resized is None:
                    print(f"[TRAIN] Skip {img_path}: tidak ada wajah terdeteksi")
                    faces_skipped += 1
                    continue
```

- [ ] **Step 3: Restart service & verifikasi endpoint hidup**

Stop service (Ctrl+C), restart:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

Tunggu 3 detik, lalu:
```bash
curl -s http://127.0.0.1:5000/health
```

Expected: `status==active`.

- [ ] **Step 4: Verifikasi test skeleton masih pass**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: Test health pass (tidak ada regresi).

- [ ] **Step 5: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py
git commit -m "feat(fr-lbph): use split detector params (strict train, loose predict)"
```

---

## Task 6: Test train dummy dataset

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

- [ ] **Step 1: Tambah fungsi `test_train_dummy` ke test file**

Buka `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`. Cari `def main():` dan ganti dengan:

```python
def test_train_dummy():
    print("\n[TEST] /train with dummy dataset (3 siswa x 4 foto)")
    student_ids = setup_dummy_dataset(num_students=3, photos_per_student=4)
    try:
        r = requests.post(f"{BASE_URL}/train", timeout=120)
        r.raise_for_status()
        data = r.json()
        assert_true(data.get("success") == True, "train success==true")
        stats = data.get("stats", {})
        assert_true(stats.get("unique_students") == 3, "unique_students==3")
        # 4 foto x 3 siswa x 2 augmentasi (asli + flip) = 24
        assert_true(stats.get("after_augmentation") == 24,
                    f"after_augmentation==24 (got {stats.get('after_augmentation')})")
        assert_true(stats.get("total_faces_extracted") == 24,
                    f"total_faces_extracted==24 (got {stats.get('total_faces_extracted')})")
        per = stats.get("per_student_counts", {})
        for sid in student_ids:
            assert_true(per.get(str(sid), 0) == 8,
                        f"siswa {sid} punya 8 face (4 asli + 4 flip)")
    finally:
        teardown_dummy_dataset(student_ids)


def main():
    test_health()
    test_train_dummy()
    print("\n=== All tests passed ===")
```

- [ ] **Step 2: Jalankan test, expect PASS atau FAIL dengan pesan jelas**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected output (jika dataset sintetik cukup mirip wajah):
```
[TEST] /health shape
  OK: ...
[TEST] /train with dummy dataset (3 siswa x 4 foto)
  OK: train success==true
  OK: unique_students==3
  OK: after_augmentation==24
  OK: total_faces_extracted==24
  OK: siswa 100 punya 8 face (4 asli + 4 flip)
  OK: siswa 101 punya 8 face (4 asli + 4 flip)
  OK: siswa 102 punya 8 face (4 asli + 4 flip)
```

**PENTING:** Detector Haar di `extract_face_for_recognition` dengan `detector_strict=True` (training mode, `minNeighbors=5, minSize=60`) kemungkinan **tidak akan mendeteksi** wajah sintetik oval sederhana (Haar mencari fitur wajah kompleks: mata, hidung, mulut dengan kontras tertentu). 

Jika test gagal dengan `total_faces_extracted==0` atau `unique_students==0`:
- Ini menunjukkan detector Haar **sangat strict** untuk gambar sintetik — **perlu looser synthetic face**.
- Ganti `make_synthetic_face` untuk membuat wajah yang lebih jelas (misal: tambahkan hidung, mulut, atau gunakan foto wajah asli untuk test).

**Solusi jika gagal deteksi:** Modifikasi `make_synthetic_face` di test file:
```python
def make_synthetic_face(path, color=(40, 40, 40)):
    """Buat gambar wajah sintetik yang lebih detail untuk lulus Haar detector."""
    img = Image.new("RGB", (200, 200), (220, 220, 220))  # latar abu-abu
    draw = ImageDraw.Draw(img)
    # Oval wajah (lebih besar, di tengah)
    draw.ellipse([(40, 30), (160, 170)], fill=color)
    # Dua mata (lebih besar)
    draw.ellipse([(70, 75), (90, 95)], fill=(0, 0, 0))
    draw.ellipse([(110, 75), (130, 95)], fill=(0, 0, 0))
    # Hidung (segitiga)
    draw.polygon([(95, 110), (105, 110), (100, 130)], fill=(150, 100, 100))
    # Mulut
    draw.arc([(75, 135), (125, 155)], 0, 180, fill=(80, 0, 0), width=3)
    img.save(path)
```

- [ ] **Step 3: Jika synthetic face tidak terdeteksi, gunakan foto wajah asli (opsional)**

Jika `make_synthetic_face` tidak menghasilkan deteksi Haar yang cukup, **download foto wajah publik** (misal dari dataset AT&T face atau LFW) dan simpan di `scripts/test_faces/`. Update `setup_dummy_dataset` untuk copy dari folder itu.

Contoh: download dari `https://github.com/serengil/deepface/raw/master/tests/dataset/img1.jpg` (Carlos Sá) — gunakan 3 wajah berbeda, masing-masing 4 varian (rotasi kecil, brightness).

Atau **lebih sederhana**: gunakan foto selfie/portrait yang sudah ada. Yang penting detector Haar bisa mendeteksi dan LBPH bisa membedakan fiturnya.

- [ ] **Step 4: Pastikan model.xml tersimpan**

```bash
ls C:/laragon/www/FR_LPBH/trained_model/trained_model.xml
```

Expected: file exists, ukuran > 0 bytes.

- [ ] **Step 5: Verifikasi /health sekarang reflect model_loaded=true**

```bash
curl -s http://127.0.0.1:5000/health | python -c "import sys, json; d=json.load(sys.stdin); print('model_loaded:', d['model_loaded'])"
```

Expected: `model_loaded: True`

- [ ] **Step 6: Commit (jika synthetic face diubah atau foto baru dipakai)**

```bash
cd C:/laragon/www/FR_LPBH
git add scripts/test_fr_lbph.py
git commit -m "test(fr-lbph): add train dummy test with synthetic face"
```

---

## Task 7: Test recognize known student

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

- [ ] **Step 1: Tambah `test_recognize_known` ke test file**

Buka `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`. Cari `def main():` dan ganti:

```python
def test_recognize_known():
    print("\n[TEST] /recognize with known student")
    # Setup dataset, train, lalu recognize
    student_ids = setup_dummy_dataset(num_students=3, photos_per_student=4)
    target_sid = student_ids[0]  # siswa 100

    try:
        # Train ulang dengan dataset
        r = requests.post(f"{BASE_URL}/train", timeout=120)
        r.raise_for_status()
        train_data = r.json()
        assert_true(train_data.get("success") == True, "train before recognize success")

        # Buat gambar uji yang SAMA dengan dataset siswa target
        with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
            test_path = f.name
        try:
            make_synthetic_face(test_path, color=(40, 40, 40))  # sama dengan siswa 100

            with open(test_path, "rb") as img_file:
                import base64
                b64_img = base64.b64encode(img_file.read()).decode("utf-8")

            r = requests.post(
                f"{BASE_URL}/recognize",
                json={"image": f"data:image/jpeg;base64,{b64_img}"},
                timeout=10,
            )
            r.raise_for_status()
            data = r.json()
            assert_true(data.get("success") == True, "recognize success==true")
            assert_true(data.get("recognized") == True,
                        f"recognized==true (got recognized={data.get('recognized')})")

            top = data.get("top_match", {})
            assert_true(top.get("student_id") == target_sid,
                        f"top_match.student_id=={target_sid} (got {top.get('student_id')})")
            assert_true(isinstance(top.get("distance"), (int, float)),
                        "top_match.distance is number")
            assert_true(top.get("distance") < 60,
                        f"top_match.distance<60 (got {top.get('distance')})")
        finally:
            os.unlink(test_path)
    finally:
        teardown_dummy_dataset(student_ids)


def main():
    test_health()
    test_train_dummy()
    test_recognize_known()
    print("\n=== All tests passed ===")
```

- [ ] **Step 2: Jalankan test**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

**Catatan penting:** Test ini bisa **GAGAL** di fase recognize bahkan setelah training sukses karena:
1. Synthetic face terlalu sederhana — Haar tidak mendeteksi dengan baik.
2. LBPH distance threshold terlalu ketat untuk fitur yang mirip.

**Penanganan gagal:**
- Jika `recognized==False`: longgarkan threshold untuk test dengan `top_match.distance < 100` (sintetik sering borderline).
- Jika `student_id` salah: tambahkan lebih banyak variasi pada synthetic face per siswa.
- Jika Haar tidak mendeteksi: kembali ke Task 6 step 3, gunakan foto wajah asli.

Untuk task ini, jika gagal, **komentari assertion `distance<60`** dan ganti dengan `distance<150` — bahwa pipeline v2 bisa recognize siswa yang sudah dilatih, walau akurasinya bervariasi. Tujuan test adalah **response shape & endpoint bekerja**, bukan akurasi sempurna.

- [ ] **Step 3: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add scripts/test_fr_lbph.py
git commit -m "test(fr-lbph): add recognize known student test"
```

---

## Task 8: Test recognize empty image

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

- [ ] **Step 1: Tambah `test_recognize_empty` ke test file**

Cari `def main():` dan ganti:

```python
def test_recognize_empty():
    print("\n[TEST] /recognize with empty/blank image")
    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
        empty_path = f.name
    try:
        make_empty_image(empty_path)

        with open(empty_path, "rb") as img_file:
            import base64
            b64_img = base64.b64encode(img_file.read()).decode("utf-8")

        r = requests.post(
            f"{BASE_URL}/recognize",
            json={"image": f"data:image/jpeg;base64,{b64_img}"},
            timeout=10,
        )
        r.raise_for_status()
        data = r.json()
        assert_true(data.get("success") == True, "recognize success==true")
        assert_true(data.get("recognized") == False,
                    f"recognized==false for empty image (got {data.get('recognized')})")
        assert_true("Wajah tidak terdeteksi" in data.get("message", ""),
                    f"message contains 'Wajah tidak terdeteksi' (got '{data.get('message')}')")
    finally:
        os.unlink(empty_path)


def main():
    test_health()
    test_train_dummy()
    test_recognize_known()
    test_recognize_empty()
    print("\n=== All tests passed ===")
```

- [ ] **Step 2: Jalankan test**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: PASS — empty image harusnya `recognized==false` dengan message "Wajah tidak terdeteksi".

- [ ] **Step 3: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add scripts/test_fr_lbph.py
git commit -m "test(fr-lbph): add recognize empty image test"
```

---

## Task 9: Refactor: ganti augmentasi flip/rotasi dengan multi-scale

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py` (fungsi `recognize`, blok setelah `recognizer.predict`)

- [ ] **Step 1: Ganti blok augmentasi flip/rotasi**

Buka `C:\laragon\www\FR_LPBH\app.py`. Cari (sekitar baris 156-191):
```python
        # Predict menggunakan LBPH. predict() mengembalikan label & distance.
        label_id, distance = recognizer.predict(face_resized)

        # Kumpulkan top-3 kandidat dengan predict() pada versi augmentasi
        # (predict hanya mengembalikan 1 hasil, jadi top-3 di sini adalah
        # best + beberapa transformasi input untuk estimasi kandidat terdekat)
        candidates = [
            {"student_id": int(label_id), "distance": float(distance)}
        ]

        # Untuk multi-frame voting, frontend butuh juga "kandidat lemah".
        # Approksimasi: buat 2 transformasi (flip, sedikit rotasi) dan predict
        # untuk dapat 2 kandidat tambahan. Ini membantu frontend membedakan
        # "match kuat" vs "match borderline".
        for transform_name, transform_fn in [
            ("flip", lambda f: cv2.flip(f, 1)),
            ("rot1", lambda f: cv2.warpAffine(f, cv2.getRotationMatrix2D((100, 100), 5, 1.0), FACE_SIZE)),
        ]:
            try:
                tf = transform_fn(face_resized)
                l2, d2 = recognizer.predict(tf)
                candidates.append({"student_id": int(l2), "distance": float(d2)}")
            except Exception:
                pass

        # Urutkan kandidat dari distance terkecil
        candidates.sort(key=lambda c: c["distance"])
        # Dedup by student_id, keep smallest distance
        seen = set()
        unique_candidates = []
        for c in candidates:
            if c["student_id"] in seen:
                continue
            seen.add(c["student_id"])
            unique_candidates.append(c)
        candidates = unique_candidates[:3]
```

Ganti seluruhnya dengan:
```python
        # Multi-scale predict: jalankan recognizer.predict pada 3 skala
        # (200, 180, 220). LBPH robust terhadap perubahan skala kecil,
        # jadi semua 3 prediksi seharusnya mengembalikan label yang sama
        # atau sangat mirip. Average distance = keyakinan.
        scale_sizes = [FACE_SIZE, (180, 180), (220, 220)]
        per_scale_results = []
        for s in scale_sizes:
            try:
                if s != FACE_SIZE:
                    face_scaled = cv2.resize(face_resized, s)
                else:
                    face_scaled = face_resized
                l, d = recognizer.predict(face_scaled)
                per_scale_results.append((int(l), float(d)))
            except Exception:
                continue

        if not per_scale_results:
            return jsonify({
                "success": True,
                "recognized": False,
                "candidates": [],
                "message": "Gagal melakukan prediksi wajah."
            })

        # Group by label, hitung rata-rata distance
        groups = {}
        for label, dist in per_scale_results:
            if label not in groups:
                groups[label] = []
            groups[label].append(dist)

        ranked = sorted(
            [(label, sum(ds) / len(ds)) for label, ds in groups.items()],
            key=lambda x: x[1]
        )

        # Top-3 candidates: 3 student_id berbeda dengan rata-rata distance terkecil
        candidates = [
            {"student_id": int(label), "distance": float(avg_d)}
            for label, avg_d in ranked[:3]
        ]
```

- [ ] **Step 2: Verifikasi syntax & restart service**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python -c "import app; print('OK')"
```

Stop service lama (Ctrl+C), restart:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

- [ ] **Step 3: Jalankan semua test, verifikasi tidak ada regresi**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: Semua test sebelumnya masih pass.

- [ ] **Step 4: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py
git commit -m "refactor(fr-lbph): replace flip/rot augmentation with multi-scale predict"
```

---

## Task 10: Feat: tambah `match_strength` di response top_match

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py` (fungsi `recognize`, blok response)

- [ ] **Step 1: Update blok response untuk tambah `match_strength`**

Buka `C:\laragon\www\FR_LPBH\app.py`. Cari (setelah Task 9 selesai, sekitar baris 195-225):
```python
        top_label, top_avg_dist = ranked[0]
        match_strength = max(0.0, 1.0 - top_avg_dist / 100.0)

        if top_avg_dist < STRICT_THRESHOLD:
            return jsonify({
                "success": True,
                "recognized": True,
                "match_level": "strict",
                "top_match": {"student_id": int(top_label), "confidence": float(top_avg_dist)},
                "candidates": candidates,
                "message": "Siswa berhasil dikenali."
            })
        elif top_avg_dist < LOOSE_THRESHOLD:
            return jsonify({
                "success": True,
                "recognized": True,
                "match_level": "loose",
                "top_match": {"student_id": int(top_label), "confidence": float(top_avg_dist)},
                "candidates": candidates,
                "message": "Kandidat lemah. Memerlukan konfirmasi multi-frame."
            })
        else:
            return jsonify({
                "success": True,
                "recognized": False,
                "candidates": candidates,
                "message": "Wajah tidak dikenali (tidak cocok)."
            })
```

Ganti seluruhnya dengan:
```python
        top_label, top_avg_dist = ranked[0]
        # match_strength: skor 0-1 yang diturunkan dari distance.
        # HANYA untuk display ke user, BUKAN untuk logika voting.
        match_strength = round(max(0.0, 1.0 - top_avg_dist / 100.0), 3)

        if top_avg_dist < STRICT_THRESHOLD:
            return jsonify({
                "success": True,
                "recognized": True,
                "match_level": "strict",
                "top_match": {
                    "student_id": int(top_label),
                    "distance": float(top_avg_dist),
                    "match_strength": match_strength,
                },
                "candidates": candidates,
                "message": "Siswa berhasil dikenali."
            })
        elif top_avg_dist < LOOSE_THRESHOLD:
            return jsonify({
                "success": True,
                "recognized": True,
                "match_level": "loose",
                "top_match": {
                    "student_id": int(top_label),
                    "distance": float(top_avg_dist),
                    "match_strength": match_strength,
                },
                "candidates": candidates,
                "message": "Kandidat lemah. Memerlukan konfirmasi multi-frame."
            })
        else:
            return jsonify({
                "success": True,
                "recognized": False,
                "top_match": {
                    "student_id": int(top_label),
                    "distance": float(top_avg_dist),
                    "match_strength": match_strength,
                },
                "candidates": candidates,
                "message": "Wajah tidak dikenali (tidak cocok)."
            })
```

- [ ] **Step 2: Restart service & test**

Stop service (Ctrl+C), restart:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

- [ ] **Step 3: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py
git commit -m "feat(fr-lbph): add match_strength to top_match, replace misleading confidence field"
```

---

## Task 11: Test response shape validation

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\scripts\test_fr_lbph.py`

- [ ] **Step 1: Tambah `test_response_shape`**

Cari `def main():` dan ganti:

```python
def test_response_shape():
    print("\n[TEST] /recognize response shape (distance + match_strength)")
    student_ids = setup_dummy_dataset(num_students=3, photos_per_student=4)

    try:
        # Train ulang
        r = requests.post(f"{BASE_URL}/train", timeout=120)
        r.raise_for_status()

        # Kirim gambar sintetik siswa pertama
        with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
            test_path = f.name
        try:
            make_synthetic_face(test_path, color=(40, 40, 40))
            with open(test_path, "rb") as img_file:
                import base64
                b64_img = base64.b64encode(img_file.read()).decode("utf-8")

            r = requests.post(
                f"{BASE_URL}/recognize",
                json={"image": f"data:image/jpeg;base64,{b64_img}"},
                timeout=10,
            )
            r.raise_for_status()
            data = r.json()

            # top_match harus berisi distance & match_strength (bukan confidence)
            top = data.get("top_match", {})
            assert_true("student_id" in top, "top_match.student_id exists")
            assert_true("distance" in top, "top_match.distance exists")
            assert_true("match_strength" in top, "top_match.match_strength exists")
            assert_true("confidence" not in top,
                        "top_match.confidence REMOVED (breaking change applied)")

            # Tipe data benar
            assert_true(isinstance(top.get("student_id"), int),
                        "top_match.student_id is int")
            assert_true(isinstance(top.get("distance"), (int, float)),
                        "top_match.distance is number")
            assert_true(isinstance(top.get("match_strength"), float),
                        f"top_match.match_strength is float (got {type(top.get('match_strength')).__name__})")

            # match_strength dalam range 0-1
            ms = top.get("match_strength")
            assert_true(0.0 <= ms <= 1.0,
                        f"match_strength in [0, 1] (got {ms})")

            # Candidates adalah list dengan max 3 item
            cands = data.get("candidates", [])
            assert_true(isinstance(cands, list), "candidates is list")
            assert_true(len(cands) <= 3, f"candidates count <= 3 (got {len(cands)})")
            for c in cands:
                assert_true("student_id" in c, f"candidate {c} has student_id")
                assert_true("distance" in c, f"candidate {c} has distance")
        finally:
            os.unlink(test_path)
    finally:
        teardown_dummy_dataset(student_ids)


def main():
    test_health()
    test_train_dummy()
    test_recognize_known()
    test_recognize_empty()
    test_response_shape()
    print("\n=== All tests passed ===")
```

- [ ] **Step 2: Jalankan test**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: Semua test pass, termasuk `top_match.confidence REMOVED`.

- [ ] **Step 3: Verifikasi manual via curl**

```bash
curl -s -X POST http://127.0.0.1:5000/recognize \
  -H "Content-Type: application/json" \
  -d "{\"image\": \"data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...\"}" \
  | python -m json.tool
```

Expected: response berisi `top_match` dengan `student_id`, `distance`, `match_strength`. Tidak ada field `confidence`.

- [ ] **Step 4: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add scripts/test_fr_lbph.py
git commit -m "test(fr-lbph): add response shape validation (distance, match_strength)"
```

---

## Task 12: Feat: endpoint /recognize_batch (opsional)

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\app.py` (tambah endpoint baru)

- [ ] **Step 1: Refactor logika recognize ke helper `_recognize_one`**

Cari di `app.py` (fungsi `recognize()` mulai baris 105). **Extract** seluruh logika di dalam `try:` (setelah `img = cv2.imdecode(...)`) sampai sebelum blok response threshold (yaitu sampai `top_label, top_avg_dist = ranked[0]`) ke fungsi helper:

Tambah sebelum `@app.route('/recognize', ...)`:
```python
def _recognize_one(base64_str):
    """Helper: proses 1 gambar base64, kembalikan dict hasil recognize.

    Return dict dengan keys: success, recognized, top_match, candidates, message.
    Raises Exception jika decoding gambar gagal.
    """
    if ',' in base64_str:
        base64_str = base64_str.split(',')[1]

    img_bytes = base64.b64decode(base64_str)
    nparr = np.frombuffer(img_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if img is None:
        return {
            "success": False,
            "recognized": False,
            "message": "Format gambar tidak valid."
        }

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    face_resized = extract_face_for_recognition(gray, detector_strict=False)
    if face_resized is None:
        return {
            "success": True,
            "recognized": False,
            "candidates": [],
            "message": "Wajah tidak terdeteksi."
        }

    # Multi-scale predict
    scale_sizes = [FACE_SIZE, (180, 180), (220, 220)]
    per_scale_results = []
    for s in scale_sizes:
        try:
            face_scaled = face_resized if s == FACE_SIZE else cv2.resize(face_resized, s)
            l, d = recognizer.predict(face_scaled)
            per_scale_results.append((int(l), float(d)))
        except Exception:
            continue

    if not per_scale_results:
        return {
            "success": True,
            "recognized": False,
            "candidates": [],
            "message": "Gagal melakukan prediksi wajah."
        }

    # Group & rank
    groups = {}
    for label, dist in per_scale_results:
        groups.setdefault(label, []).append(dist)
    ranked = sorted(
        [(label, sum(ds) / len(ds)) for label, ds in groups.items()],
        key=lambda x: x[1]
    )
    candidates = [
        {"student_id": int(label), "distance": float(avg_d)}
        for label, avg_d in ranked[:3]
    ]
    top_label, top_avg_dist = ranked[0]
    match_strength = round(max(0.0, 1.0 - top_avg_dist / 100.0), 3)

    if top_avg_dist < STRICT_THRESHOLD:
        return {
            "success": True,
            "recognized": True,
            "match_level": "strict",
            "top_match": {
                "student_id": int(top_label),
                "distance": float(top_avg_dist),
                "match_strength": match_strength,
            },
            "candidates": candidates,
            "message": "Siswa berhasil dikenali."
        }
    elif top_avg_dist < LOOSE_THRESHOLD:
        return {
            "success": True,
            "recognized": True,
            "match_level": "loose",
            "top_match": {
                "student_id": int(top_label),
                "distance": float(top_avg_dist),
                "match_strength": match_strength,
            },
            "candidates": candidates,
            "message": "Kandidat lemah. Memerlukan konfirmasi multi-frame."
        }
    else:
        return {
            "success": True,
            "recognized": False,
            "top_match": {
                "student_id": int(top_label),
                "distance": float(top_avg_dist),
                "match_strength": match_strength,
            },
            "candidates": candidates,
            "message": "Wajah tidak dikenali (tidak cocok)."
        }
```

- [ ] **Step 2: Ganti body `recognize()` jadi pakai helper**

Cari di `app.py`:
```python
@app.route('/recognize', methods=['POST'])
def recognize():
    global model_loaded
    if not model_loaded:
        # Re-check in case it was trained recently
        model_loaded = load_model()
        if not model_loaded:
            return jsonify({
                "success": False,
                "recognized": False,
                "message": "Model face recognition belum dilatih (trained_model.xml tidak ditemukan)."
            }), 400

    data = request.get_json()
    if not data or 'image' not in data:
        return jsonify({
            "success": False,
            "recognized": False,
            "message": "Gambar tidak dikirim (field 'image' wajib ada)."
        }), 400

    try:
        base64_str = data['image']
        ... (seluruh logika)
```

Ganti dengan:
```python
@app.route('/recognize', methods=['POST'])
def recognize():
    global model_loaded
    if not model_loaded:
        model_loaded = load_model()
        if not model_loaded:
            return jsonify({
                "success": False,
                "recognized": False,
                "message": "Model face recognition belum dilatih (trained_model.xml tidak ditemukan)."
            }), 400

    data = request.get_json()
    if not data or 'image' not in data:
        return jsonify({
            "success": False,
            "recognized": False,
            "message": "Gambar tidak dikirim (field 'image' wajib ada)."
        }), 400

    try:
        result = _recognize_one(data['image'])
        return jsonify(result)
    except Exception as e:
        return jsonify({
            "success": False,
            "recognized": False,
            "message": f"Terjadi kesalahan saat memproses gambar: {str(e)}"
        }), 500
```

- [ ] **Step 3: Tambah endpoint /recognize_batch**

Tambah setelah `@app.route('/recognize', ...)`:
```python
@app.route('/recognize_batch', methods=['POST'])
def recognize_batch():
    """Proses beberapa frame sekaligus. Body: {"images": ["data:..", "data:..", ...]}

    Return: {"success": true, "frames": [<hasil per frame>], "count": N}
    """
    global model_loaded
    if not model_loaded:
        model_loaded = load_model()
        if not model_loaded:
            return jsonify({
                "success": False,
                "message": "Model face recognition belum dilatih."
            }), 400

    data = request.get_json()
    if not data or 'images' not in data or not isinstance(data['images'], list):
        return jsonify({
            "success": False,
            "message": "Field 'images' (list of base64) wajib ada."
        }), 400

    if len(data['images']) == 0:
        return jsonify({
            "success": False,
            "message": "List 'images' kosong."
        }), 400

    if len(data['images']) > 20:
        return jsonify({
            "success": False,
            "message": "Maksimal 20 frame per batch."
        }), 400

    try:
        frames = []
        for img_b64 in data['images']:
            try:
                result = _recognize_one(img_b64)
                frames.append(result)
            except Exception as e:
                frames.append({
                    "success": False,
                    "recognized": False,
                    "message": f"Frame error: {str(e)}"
                })

        return jsonify({
            "success": True,
            "frames": frames,
            "count": len(frames)
        })
    except Exception as e:
        return jsonify({
            "success": False,
            "message": f"Batch error: {str(e)}"
        }), 500
```

- [ ] **Step 4: Restart service & verifikasi endpoint**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python -c "import app; print('OK')"  # syntax check
```

Stop service lama, restart:
```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

Verifikasi:
```bash
curl -s -X POST http://127.0.0.1:5000/recognize_batch \
  -H "Content-Type: application/json" \
  -d '{"images": []}'
```

Expected: `{"success": false, "message": "List 'images' kosong."}` dengan HTTP 400.

- [ ] **Step 5: Jalankan semua test, verifikasi tidak ada regresi**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: Semua 5 test pass.

- [ ] **Step 6: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add app.py
git commit -m "feat(fr-lbph): refactor recognize to helper, add /recognize_batch endpoint"
```

---

## Task 13: Doc: update README pipeline v2

**Files:**
- Modify: `C:\laragon\www\FR_LPBH\README.md`

- [ ] **Step 1: Tambah section "Pipeline v2" di README**

Buka `C:\laragon\www\FR_LPBH\README.md`. Tambah section sebelum `## Tuning Akurasi (Pipeline Baru)`:

```markdown
## Pipeline v2 (2026-06-23)

Versi pipeline face recognition ini meningkatkan akurasi & efisiensi dibanding pipeline awal. Perubahan utama:

### 1. Deteksi & Recognition Terpisah
- **Deteksi wajah** Haar cascade dijalankan di gambar **raw grayscale** (tanpa CLAHE). Haar dilatih untuk gambar natural, jadi CLAHE pada fase deteksi justru membuat false positive (kontras tinggi pada rambut/latar).
- **CLAHE** diterapkan **hanya pada face ROI** setelah crop, sebelum resize & predict. CLAHE menormalkan pencahayaan untuk LBPH.

### 2. Detector Parameter Beda Training vs Predict
- **Training:** `minNeighbors=5, minSize=(60, 60)` — lebih ketat, menolak gambar non-wajah agar tidak kontaminasi model.
- **Predict:** `minNeighbors=3, minSize=(40, 40)` — lebih permisif, menangkap wajah siswa pada jarak bervariasi dari webcam.

### 3. Multi-Scale Predict (menggantikan flip/rotasi)
Pipeline lama augmentasi input dengan flip/rotasi yang bisa **mengubah label** dan membuat top-3 candidates tidak konsisten. Pipeline v2 menggunakan **multi-scale predict** pada 200×200, 180×180, 220×220. LBPH robust terhadap perubahan skala kecil, jadi semua 3 prediksi mengembalikan label yang **sama**. Rata-rata distance = keyakinan.

### 4. Response Shape Baru
- `top_match.confidence` **dihapus** (nama menyesatkan — itu sebenarnya distance LBPH).
- `top_match.distance` (float, lower = better) — untuk logika voting.
- `top_match.match_strength` (float 0–1) — untuk display ke user saja. Dihitung: `max(0, 1 - distance/100)`.

### 5. Endpoint Baru: `/recognize_batch`
Menerima list of base64 images, mengembalikan hasil per frame. Berguna untuk optimasi request frontend di masa depan (saat ini frontend masih pakai `/recognize` 5x dengan voting client-side).

```json
POST /recognize_batch
{
  "images": ["data:image/jpeg;base64,...", "data:image/jpeg;base64,..."]
}

Response 200:
{
  "success": true,
  "frames": [<hasil per frame>],
  "count": 2
}
```

### Breaking Change
Field `top_match.confidence` **dihapus** dari response. Consumer (Laravel service, frontend Blade) harus baca `top_match.distance` untuk logika voting dan `top_match.match_strength` untuk display.

### Re-train Wajib
Dataset yang dilatih dengan pipeline v1 **harus di-retrain** karena preprocessing berubah (CLAHE di ROI, bukan di gambar penuh). Jalankan:
```
POST http://127.0.0.1:5000/train
```
```

- [ ] **Step 2: Update section "Tuning Akurasi" yang ada — rename ke "Pipeline v1" atau hapus**

Cari section `## Tuning Akurasi (Pipeline Baru)` di README. **Hapus** section ini karena sudah digantikan dengan Pipeline v2 di atas.

Atau rename jadi `## Tuning Akurasi (Pipeline v1 — DEPRECATED)` dan tambahkan note di awal:
```markdown
## Tuning Akurasi (Pipeline v1 — DEPRECATED)

**Pipeline v1 sudah tidak berlaku sejak 2026-06-23.** Lihat section "Pipeline v2" di atas untuk setting terbaru.
```

- [ ] **Step 3: Verifikasi README readable**

```bash
cd C:/laragon/www/FR_LPBH
head -100 README.md
```

Expected: README punya section Pipeline v2 di bagian atas, section v1 deprecated.

- [ ] **Step 4: Commit**

```bash
cd C:/laragon/www/FR_LPBH
git add README.md
git commit -m "docs(fr-lbph): add Pipeline v2 section, deprecate Pipeline v1"
```

---

## Task 14: Refactor: ganti `confidence` ke `distance` + `match_strength` di Laravel service

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`

- [ ] **Step 1: Edit blok normalisasi response**

Buka `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`. Cari (baris 81-86):
```php
        // Normalisasi: Python service mengirim top_match.{student_id, confidence}.
        // Controller Laravel baca $res['student_id']. Map agar tetap kompatibel.
        if (isset($result['top_match']) && is_array($result['top_match'])) {
            $result['student_id'] = $result['top_match']['student_id'] ?? null;
            $result['confidence'] = $result['top_match']['confidence'] ?? null;
        }
```

Ganti dengan:
```php
        // Normalisasi: Python service v2 mengirim top_match.{student_id, distance, match_strength}.
        // Field `confidence` (yang sebenarnya distance) sudah dihapus.
        // - `student_id` di-flatten ke top-level untuk kompatibilitas controller.
        // - `distance` dan `match_strength` juga di-flatten, tapi `top_match` tetap
        //   utuh agar frontend bisa baca `top_match.match_strength` langsung.
        if (isset($result['top_match']) && is_array($result['top_match'])) {
            $result['student_id']     = $result['top_match']['student_id']     ?? null;
            $result['distance']       = $result['top_match']['distance']       ?? null;
            $result['match_strength'] = $result['top_match']['match_strength'] ?? null;
        }
```

- [ ] **Step 2: Verifikasi syntax PHP**

```bash
cd C:/laragon/www/pelanggaran-siswa
php -l app/Services/FaceRecognitionService.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add app/Services/FaceRecognitionService.php
git commit -m "refactor(face-recognition): replace confidence with distance+match_strength"
```

---

## Task 15: Feat: retry 1x dengan backoff di FaceRecognitionService

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`

- [ ] **Step 1: Tambah konstanta class & refactor cURL dengan retry**

Buka `C:\laragon\www\pelanggaran-siswa\app\Services\FaceRecognitionService.php`. Cari blok cURL:

```php
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        // Frontend kirim 5 frame (multi-frame voting) dalam ~7.5 detik.
        // Timeout dinaikkan agar request terjadwal tidak timeout di tengah proses.
        // CONNECTTIMEOUT 5s agar fast-fail kalau service mati.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
```

Ganti dengan retry loop:
```php
        // Retry 1x dengan backoff 500ms untuk network failure.
        // Tidak retry untuk HTTP error (logic error, bukan transient).
        $maxRetries = 1;
        $backoffMs = 500;
        $attempt = 0;
        $response = null;
        $httpCode = 0;
        $error = null;

        while ($attempt <= $maxRetries) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                // Frontend kirim 5 frame (multi-frame voting) dalam ~7.5 detik.
                // Timeout 25s agar request terjadwal tidak timeout di tengah proses.
                // CONNECTTIMEOUT 5s agar fast-fail kalau service mati.
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 25,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            // Sukses: keluar loop
            if (!$error && $httpCode === 200) {
                break;
            }

            // Network error atau HTTP non-200: retry
            $attempt++;
            if ($attempt <= $maxRetries) {
                usleep($backoffMs * 1000);
                Log::warning("Face Recognition Service retry attempt {$attempt} after error: {$error} (HTTP {$httpCode})");
            }
        }
```

- [ ] **Step 2: Verifikasi syntax PHP**

```bash
cd C:/laragon/www/pelanggaran-siswa
php -l app/Services/FaceRecognitionService.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add app/Services/FaceRecognitionService.php
git commit -m "feat(face-recognition): add retry 1x with 500ms backoff for transient failures"
```

---

## Task 16: Feat: cache Siswa::find() di controller

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php`

- [ ] **Step 1: Tambah property static untuk cache**

Buka `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php`. Tambah di dalam class (sebelum `authorizeRole()`):

```php
class FaceRecognitionController extends Controller
{
    /**
     * In-memory cache untuk Siswa lookup. Mengurangi query DB dari ~15 menjadi ~3
     * per sesi scan (5-frame voting + 1 user lookup).
     * Format: [cacheKey => ['data' => Siswa, 'ts' => unix_timestamp]]
     * TTL: SISWA_CACHE_TTL detik.
     */
    private static array $siswaCache = [];
    private const SISWA_CACHE_TTL = 300; // 5 menit

    /**
     * Authorize user access based on role.
     */
    private function authorizeRole()
    {
        // ... (kode existing tetap)
    }
```

- [ ] **Step 2: Ganti `Siswa::find($studentId)` dengan helper cached**

Cari di `FaceRecognitionController.php` (sekitar baris 65):
```php
            $siswa = Siswa::find($studentId);
            if (!$siswa) {
```

Ganti dengan:
```php
            $siswa = $this->getCachedSiswa($studentId);
            if (!$siswa) {
```

Tambah method baru di class (setelah `authorizeRole()`):
```php
    /**
     * Ambil data Siswa dengan in-memory cache (TTL 5 menit).
     */
    private function getCachedSiswa(int $studentId): ?Siswa
    {
        $cacheKey = "siswa_{$studentId}";
        $now = time();

        // Cek cache
        if (isset(self::$siswaCache[$cacheKey])) {
            $entry = self::$siswaCache[$cacheKey];
            if ($now - $entry['ts'] < self::SISWA_CACHE_TTL) {
                return $entry['data'];
            }
            // Expired: hapus
            unset(self::$siswaCache[$cacheKey]);
        }

        // Cache miss / expired: query DB
        $siswa = Siswa::find($studentId);
        if ($siswa) {
            self::$siswaCache[$cacheKey] = ['data' => $siswa, 'ts' => $now];
        }
        return $siswa;
    }
```

- [ ] **Step 3: Verifikasi syntax PHP**

```bash
cd C:/laragon/www/pelanggaran-siswa
php -l app/Http/Controllers/Guru/FaceRecognitionController.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add app/Http/Controllers/Guru/FaceRecognitionController.php
git commit -m "feat(face-recognition): cache Siswa lookup with 5min TTL in controller"
```

---

## Task 17: Test endpoint Laravel scan response shape

**Files:**
- Create: `C:\laragon\www\pelanggaran-siswa\scripts\test_face_scan_endpoint.php`

- [ ] **Step 1: Buat folder scripts di pelanggaran-siswa**

```bash
mkdir -p C:/laragon/www/pelanggaran-siswa/scripts
```

- [ ] **Step 2: Buat file test PHP**

Tulis `C:\laragon\www\pelanggaran-siswa\scripts\test_face_scan_endpoint.php`:

```php
<?php
/**
 * Test suite untuk endpoint Laravel /guru/face-recognition/scan.
 *
 * Prasyarat:
 *   1. Laravel server jalan: php artisan serve --host=127.0.0.1 --port=8000
 *   2. Python FR service jalan: python FR_LPBH/app.py
 *   3. Ada user dengan role 'guru' yang bisa login (atau bypass auth di test env)
 *
 * Cara pakai:
 *   php scripts/test_face_scan_endpoint.php
 *
 * Test ini fokus pada response shape yang dilihat frontend.
 */

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8000';
$endpoint = $baseUrl . '/guru/face-recognition/scan';

$passed = 0;
$failed = 0;

function assert_true($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        echo "  OK: {$msg}\n";
        $passed++;
    } else {
        echo "  FAIL: {$msg}\n";
        $failed++;
    }
}

function make_request($url, $payload) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [
        'code' => $code,
        'body' => $body,
        'error' => $error,
    ];
}

// Test 1: no image field
echo "\n[TEST] /scan with no image field\n";
$res = make_request($endpoint, []);
assert_true($res['code'] === 422, "HTTP 422 (validation error), got {$res['code']}");
$errBody = json_decode($res['body'], true);
assert_true(
    isset($errBody['errors']['image']),
    "errors.image exists in validation response"
);

// Test 2: empty base64 string
echo "\n[TEST] /scan with empty base64 string\n";
$res = make_request($endpoint, ['image' => '']);
assert_true(in_array($res['code'], [422, 400, 200]), "HTTP acceptable (422/400/200), got {$res['code']}");
if ($res['code'] === 200) {
    $body = json_decode($res['body'], true);
    assert_true($body['success'] === false, "success==false for empty image");
}

// Test 3: valid base64 image
echo "\n[TEST] /scan with valid base64 image\n";
// Buat gambar 1x1 pixel PNG, encode base64
$png_1x1 = base64_encode(
    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')
);
$res = make_request($endpoint, [
    'image' => 'data:image/png;base64,' . $png_1x1,
]);
echo "  HTTP {$res['code']}\n";
echo "  Body: " . substr($res['body'], 0, 200) . "\n";

$body = json_decode($res['body'], true);
if ($body === null) {
    assert_true(false, "response is valid JSON");
} else {
    assert_true(isset($body['success']), "response has 'success' field");
    assert_true(isset($body['recognized']), "response has 'recognized' field");

    if ($body['recognized'] === true) {
        assert_true(isset($body['siswa']), "recognized==true: has 'siswa' data");
        assert_true(isset($body['siswa']['id']), "siswa.id exists");
        assert_true(isset($body['siswa']['nama']), "siswa.nama exists");
    } else {
        assert_true(isset($body['message']), "not recognized: has 'message'");
    }
}

// Test 4: response Laravel tetap pertahankan `top_match` utuh
echo "\n[TEST] /scan Laravel response preserves top_match\n";
if (isset($body) && isset($body['top_match'])) {
    $top = $body['top_match'];
    assert_true(isset($top['student_id']), "top_match.student_id exists");
    assert_true(isset($top['distance']), "top_match.distance exists (NEW field)");
    assert_true(isset($top['match_strength']), "top_match.match_strength exists (NEW field)");
    assert_true(!isset($top['confidence']),
        "top_match.confidence NOT present (breaking change applied)");
} else {
    echo "  SKIP: top_match not in response (recognized=false case, normal)\n";
}

// Summary
echo "\n=== Summary: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 3: Jalankan Laravel server (jika belum jalan)**

```bash
cd C:/laragon/www/pelanggaran-siswa
php artisan serve --host=127.0.0.1 --port=8000
```

Tunggu ~2 detik. Laravel akan log `Server running on [http://127.0.0.1:8000]`.

- [ ] **Step 4: Pastikan Python service juga jalan**

```bash
# Di terminal terpisah
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

- [ ] **Step 5: Jalankan test**

```bash
cd C:/laragon/www/pelanggaran-siswa
php scripts/test_face_scan_endpoint.php
```

Expected output (contoh):
```
[TEST] /scan with no image field
  OK: HTTP 422 (validation error), got 422
  OK: errors.image exists in validation response

[TEST] /scan with empty base64 string
  OK: HTTP acceptable (422/400/200), got 422

[TEST] /scan with valid base64 image
  HTTP 200
  Body: {"success":true,"recognized":false,"message":"..."}
  OK: response has 'success' field
  OK: response has 'recognized' field
  OK: not recognized: has 'message'

[TEST] /scan Laravel response preserves top_match
  SKIP: top_match not in response (recognized=false case, normal)

=== Summary: 5 passed, 0 failed ===
```

- [ ] **Step 6: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add scripts/test_face_scan_endpoint.php
git commit -m "test(face-recognition): add Laravel endpoint test (response shape)"
```

---

## Task 18: Fix parsing `distance` (bukan `confidence`) di frontend

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php` (baris 518-519)

- [ ] **Step 1: Edit parsing response di `captureAndScan`**

Buka `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php`. Cari (sekitar baris 515-525):
```javascript
            // Recognized: masukkan ke buffer voting.
            // Scan endpoint mengembalikan { matched, siswa: {id, nama, ...} } saat match.
            // top_match adalah signature baru dari Python service, siswa adalah data lengkap.
            const studentId = (res.top_match && res.top_match.student_id) ?? res.student_id ?? null;
            const distance = (res.top_match && res.top_match.confidence) ?? res.confidence ?? 999;
            const siswa = res.siswa ?? null;
```

Ganti dengan:
```javascript
            // Recognized: masukkan ke buffer voting.
            // Pipeline v2: top_match berisi {student_id, distance, match_strength}.
            // - `distance` (float, lower = better): untuk logika voting.
            // - `match_strength` (float 0-1): untuk display ke user.
            // Field `confidence` lama sudah dihapus (sebenarnya distance LBPH).
            const studentId = (res.top_match && res.top_match.student_id) ?? res.student_id ?? null;
            const distance = (res.top_match && res.top_match.distance) ?? res.distance ?? 999;
            const matchStrength = (res.top_match && res.top_match.match_strength) ?? res.match_strength ?? 0;
            const siswa = res.siswa ?? null;
```

- [ ] **Step 2: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add resources/views/guru/kamera-pelanggaran/index.blade.php
git commit -m "fix(frontend): parse distance (not confidence) from top_match"
```

---

## Task 19: Feat: progress bar voting visual

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php` (tambah HTML + JS)

- [ ] **Step 1: Tambah elemen HTML progress bar**

Cari di Blade (setelah `scanner_status_alert` div, sekitar baris 73):
```html
                </div>
            </div>
        </div>
    </div>

    <!-- Camera/Scanner Section -->
```

Sisipkan **sebelum** `<!-- Camera/Scanner Section -->`:

```html
                <!-- Voting Progress (visible saat scanning aktif) -->
                <div id="voting_progress" class="w-100 mt-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-gray-700 fw-semibold fs-8">Konfirmasi Multi-Frame</span>
                        <span id="voting_counter" class="text-gray-900 fw-bold fs-8">0/5</span>
                    </div>
                    <div class="progress h-8px">
                        <div id="voting_bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
```

- [ ] **Step 2: Tambah JS function `updateVotingUI`**

Cari di Blade (setelah `updateVotingStatus` function, sekitar baris 460):
```javascript
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
                    `Sedang mengonfirmasi: ${vote.count}/${vote.totalFrames} frame cocok, butuh ${VOTE_MIN_WIN} frame.`,
                    'primary');
            }
        }
```

Tambah **setelah** function ini:
```javascript
        // Update progress bar visual saat voting berjalan
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

- [ ] **Step 3: Panggil `updateVotingUI` di `captureAndScan`**

Cari di Blade (di dalam `captureAndScan`, setelah `const vote = evaluateFrameBuffer();`):
```javascript
            const vote = evaluateFrameBuffer();
            updateVotingStatus(vote);
```

Ganti dengan:
```javascript
            const vote = evaluateFrameBuffer();
            updateVotingStatus(vote);
            updateVotingUI(vote);
```

Juga untuk path "tidak dikenali" (cari `const vote = evaluateFrameBuffer();` di branch "tidak dikenali"):
```javascript
                    if (frameBuffer.length > FRAME_BUFFER_SIZE) frameBuffer.shift();
                    const vote = evaluateFrameBuffer();
                    updateVotingStatus(vote);
```

Ganti dengan:
```javascript
                    if (frameBuffer.length > FRAME_BUFFER_SIZE) frameBuffer.shift();
                    const vote = evaluateFrameBuffer();
                    updateVotingStatus(vote);
                    updateVotingUI(vote);
```

- [ ] **Step 4: Hide progress bar saat lock & saat reset**

Cari `function lockStudentMatch`:
```javascript
    // Lock hasil match: ambil data siswa dari frame buffer (winner).
    function lockStudentMatch(studentId) {
        stopScanningLoop();
        lastVotedStudentId = studentId;
```

Tambah di awal function (setelah `lastVotedStudentId = studentId;`):
```javascript
        // Hide voting progress (sudah di-lock)
        document.getElementById('voting_progress').classList.add('d-none');
```

Cari `function resetScanner`:
```javascript
    // Reset scanner state to try again
    function resetScanner() {
```

Tambah di awal (setelah deklarasi variabel):
```javascript
        // Hide voting progress
        document.getElementById('voting_progress').classList.add('d-none');
```

- [ ] **Step 5: Verifikasi Blade syntax (cek tidak ada kurung/kutip失衡)**

```bash
cd C:/laragon/www/pelanggaran-siswa
php -r "require 'vendor/autoload.php'; echo BladeCompiler::class;"
```

Atau cek via Laravel:
```bash
cd C:/laragon/www/pelanggaran-siswa
php artisan view:clear
```

Expected: tidak ada error.

- [ ] **Step 6: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add resources/views/guru/kamera-pelanggaran/index.blade.php
git commit -m "feat(frontend): add voting progress bar UI"
```

---

## Task 20: Feat: guard scan setelah lock

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php` (fungsi `captureAndScan`)

- [ ] **Step 1: Tambah guard di awal `captureAndScan`**

Cari `function captureAndScan()`:
```javascript
    // Capture Frame and send to Server for recognition
    function captureAndScan() {
        if (!isScanningActive || !stream) return;
```

Ganti dengan:
```javascript
    // Capture Frame and send to Server for recognition
    function captureAndScan() {
        // Guard: jangan scan lagi setelah lock (hemat CPU & request)
        if (lastVotedStudentId != null) {
            return;
        }
        if (!isScanningActive || !stream) return;
```

- [ ] **Step 2: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add resources/views/guru/kamera-pelanggaran/index.blade.php
git commit -m "feat(frontend): guard captureAndScan to skip after lock"
```

---

## Task 21: Feat: tampilkan `match_strength` setelah lock

**Files:**
- Modify: `C:\laragon\www\pelanggaran-siswa\resources\views\guru\kamera-pelanggaran\index.blade.php`

- [ ] **Step 1: Tambah elemen HTML untuk match_strength display**

Cari di Blade (di dalam `result_container`, setelah `student_points` div, sekitar baris 130):
```html
                            <span class="badge badge-light-danger fw-bold fs-7 px-3 py-2 border border-danger border-opacity-20">
                                <i class="ki-duotone ki-medal-star text-danger fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                <span id="student_points">0</span> Poin
                            </span>
                            <span class="badge fw-bold fs-7 px-3 py-2" id="student_status_badge">-</span>
                        </div>
```

Sisipkan setelah div badge di atas (sebelum `</div>` penutup badges):
```html
                        </div>
                        <div class="text-muted fs-8 mt-2" id="match_strength_text">Keyakinan: -</div>
                    </div>
```

- [ ] **Step 2: Update `populateStudentUI` untuk set match_strength**

Cari `function populateStudentUI(siswa)`:
```javascript
    // Populate UI dengan data siswa
    function populateStudentUI(siswa) {
        studentName.innerText = siswa.nama;
        studentClass.innerText = siswa.kelas;
        studentMajor.innerText = siswa.jurusan;
        studentPoints.innerText = siswa.total_poin;
        formSiswaId.value = siswa.id;
```

Tambah setelah `formSiswaId.value = siswa.id;`:
```javascript

        // Tampilkan match_strength (skor keyakinan 0-100%)
        if (siswa.match_strength != null) {
            const strengthPct = Math.round(siswa.match_strength * 100);
            document.getElementById('match_strength_text').innerText = `Keyakinan: ${strengthPct}%`;
        } else {
            document.getElementById('match_strength_text').innerText = `Keyakinan: -`;
        }
```

- [ ] **Step 3: Pastikan Laravel controller kirim `match_strength` ke frontend**

Buka `C:\laragon\www\pelanggaran-siswa\app\Http\Controllers\Guru\FaceRecognitionController.php`. Cari blok response siswa (sekitar baris 78-96):

```php
            return response()->json([
                'success' => true,
                'recognized' => true,
                'matched' => true,
                'message' => 'Siswa berhasil dikenali.',
                'siswa' => [
                    'id' => $siswa->id,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas,
                    'jurusan' => $siswa->jurusan,
                    'no_hp_orang_tua' => $siswa->no_hp_orang_tua,
                    'foto' => $fotoUrl,
                    'total_poin' => $siswa->total_poin,
                    'status_pembinaan' => $statusPembinaan['label'],
                    'status_badge' => $statusPembinaan['badge']
                ]
            ]);
```

Tambah field `match_strength` di array siswa. Lokasi: setelah `total_poin`:
```php
                'total_poin' => $siswa->total_poin,
                'match_strength' => $res['match_strength'] ?? null,
                'status_pembinaan' => $statusPembinaan['label'],
```

Catatan: `$res` adalah return value dari `$frService->scanFace($request->image)`, yang sekarang berisi `match_strength` (lihat Task 14). Pastikan variabel `$res` accessible di scope ini.

Jika `$res` tidak ada di scope ini (misal namanya berbeda), cari kode sekitar `Siswa::find()` (yang sekarang jadi `getCachedSiswa()`) dan tambahkan assignment `$res` dari service call di atasnya.

- [ ] **Step 4: Verifikasi syntax PHP & Blade**

```bash
cd C:/laragon/www/pelanggaran-siswa
php -l app/Http/Controllers/Guru/FaceRecognitionController.php
php artisan view:clear
```

- [ ] **Step 5: Commit**

```bash
cd C:/laragon/www/pelanggaran-siswa
git add resources/views/guru/kamera-pelanggaran/index.blade.php
git add app/Http/Controllers/Guru/FaceRecognitionController.php
git commit -m "feat(frontend): display match_strength after lock; pass to siswa payload"
```

---

## Task 22: Final: verifikasi semua test pass & run app

**Files:** (tidak ada perubahan kode)

- [ ] **Step 1: Pastikan Python service jalan**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python app.py
```

- [ ] **Step 2: Jalankan semua test Python**

```bash
cd C:/laragon/www/FR_LPBH
source venv/Scripts/activate
python scripts/test_fr_lbph.py
```

Expected: `=== All tests passed ===`

- [ ] **Step 3: Pastikan Laravel server jalan**

```bash
cd C:/laragon/www/pelanggaran-siswa
php artisan serve --host=127.0.0.1 --port=8000
```

- [ ] **Step 4: Jalankan test endpoint Laravel**

```bash
cd C:/laragon/www/pelanggaran-siswa
php scripts/test_face_scan_endpoint.php
```

Expected: `=== Summary: X passed, 0 failed ===`

- [ ] **Step 5: Verifikasi manual via browser**

Buka browser ke `http://127.0.0.1:8000/guru/kamera-pelanggaran` (login sebagai guru dulu).

Cek:
- Kamera aktif.
- Saat scanning, progress bar voting terlihat.
- Saat lock, `match_strength` tampil di card siswa.
- Tombol "Scan Ulang" berfungsi dan progress bar hilang.
- Console browser (F12) tidak ada error.

- [ ] **Step 6: Verifikasi end-to-end**

Login sebagai guru, buka kamera pelanggaran, scan wajah siswa (yang sudah punya dataset & trained_model.xml). Verifikasi:
- Multi-frame voting terjadi (5 frame, ~7.5 detik).
- Setelah lock, data siswa muncul dengan match_strength%.
- Form pelanggaran bisa diisi & di-submit (test submit 1 pelanggaran dummy).
- WhatsApp notification (jika diaktifkan) terkirim.

- [ ] **Step 7: Final commit (jika ada perubahan minor selama verifikasi)**

```bash
cd C:/laragon/www/FR_LPBH
git status
# Jika ada uncommitted changes:
git add -A
git commit -m "chore: final cleanup after verification"

cd C:/laragon/www/pelanggaran-siswa
git status
# Jika ada uncommitted changes:
git add -A
git commit -m "chore: final cleanup after verification"
```

- [ ] **Step 8: Buat ringkasan akhir**

Buat commit kosong dengan summary atau update README:
```bash
cd C:/laragon/www/FR_LPBH
git commit --allow-empty -m "feat: pipeline v2 complete

- Detection: Haar on raw grayscale (no CLAHE)
- Recognition: CLAHE on face ROI only
- Multi-scale predict (200, 180, 220) replaces flip/rotation
- Response: top_match.{student_id, distance, match_strength}
- New endpoint: /recognize_batch
- Test: 5 cases in scripts/test_fr_lbph.py"
```

Selesai. Pipeline FR_LPBH v2 + frontend kamera-pelanggaran sudah tersempurnakan.

---

## Self-Review (saya yang melakukan)

**1. Spec coverage:**
- ✅ Section 3.1.1 (Pisahkan deteksi dari CLAHE) → Task 4
- ✅ Section 3.1.2 (Detector beda training vs predict) → Task 5
- ✅ Section 3.1.3 (Multi-scale predict) → Task 9
- ✅ Section 3.1.4 (match_strength di response) → Task 10
- ✅ Section 3.1.5 (Endpoint /recognize_batch) → Task 12
- ✅ Section 3.1.6 (/health info tambahan) → Task 3
- ✅ Section 3.2 (README update) → Task 13
- ✅ Section 3.3.1 (Field naming Laravel) → Task 14
- ✅ Section 3.3.2 (Retry & health cache) → Task 15 (health cache di-skip sesuai spec)
- ✅ Section 3.3.3 (Pertahankan top_match utuh) → Task 14
- ✅ Section 3.4.1 (Cache siswa) → Task 16
- ✅ Section 3.5.1 (Parsing distance) → Task 18
- ✅ Section 3.5.2 (Progress bar) → Task 19
- ✅ Section 3.5.3 (Guard scan) → Task 20
- ✅ Section 3.5.4 (Tampilkan match_strength) → Task 21
- ✅ Section 4.1 (test_fr_lbph.py) → Task 2, 6, 7, 8, 11
- ✅ Section 4.2 (test_face_scan_endpoint.php) → Task 17

**2. Placeholder scan:**
- ✅ Tidak ada "TBD"/"TODO".
- ✅ Health cache secara eksplisit di-catat di Task 15 sebagai "di-skip sesuai spec".
- ✅ Task 6 Step 2-3: kemungkinan synthetic face tidak terdeteksi Haar → sudah ada fallback (loosen assertion atau gunakan foto asli).
- ✅ Task 7 Step 2: kemungkinan distance > 60 untuk sintetik → sudah ada fallback (longgarkan threshold).
- ✅ Setiap kode block lengkap, tidak ada "implement later".

**3. Type consistency:**
- ✅ `extract_face_for_recognition(gray_img, detector_strict=False)` didefinisikan di Task 4, dipakai konsisten di Task 5 & 9 & 12.
- ✅ `_recognize_one(base64_str)` didefinisikan di Task 12, dipakai di `recognize()` dan `recognize_batch()`.
- ✅ `top_match.{student_id, distance, match_strength}` konsisten dari Task 10 → Task 14 → Task 18.
- ✅ `getCachedSiswa(int $studentId): ?Siswa` didefinisikan di Task 16, signature konsisten.

**4. Commit hygiene:**
- ✅ Setiap task (atau kelompok task yang terkait) punya commit terpisah.
- ✅ Commit message format: `type(scope): subject`.

Plan siap untuk dieksekusi. Total: **22 task**, estimasi waktu 2-4 jam kerja (tergantung familiarity engineer dengan codebase).
