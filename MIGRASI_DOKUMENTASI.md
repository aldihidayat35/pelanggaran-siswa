# Dokumentasi Migrasi & Instalasi

## Aplikasi: Sistem Pelanggaran Siswa + Face Recognition Service

> Versi: 2026-01 (Laravel 12 + Flask LBPH)  
> Platform target: Windows (Laragon / manual install)

---

## Daftar Isi

1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Persyaratan Sistem (Minimum)](#2-persyaratan-sistem)
3. [Langkah 1 — Clone dari GitHub](#langkah-1--clone-dari-github)
4. [Langkah 2 — Instalasi Backend (Laravel)](#langkah-2--instalasi-backend-laravel)
5. [Langkah 3 — Instalasi Frontend (Vite + Tailwind)](#langkah-3--instalasi-frontend-vite--tailwind)
6. [Langkah 4 — Konfigurasi Database](#langkah-4--konfigurasi-database)
7. [Langkah 5 — Instalasi & Jalankan Face Recognition Service (Flask)](#langkah-5--instalasi--jalankan-face-recognition-service-flask)
8. [Langkah 6 — Integrasi Kedua Aplikasi](#langkah-6--integrasi-kedua-aplikasi)
9. [Langkah 7 — Data Awal & Train Model](#langkah-7--data-awal--train-model)
10. [Cara Menjalankan Harian](#cara-menjalankan-harian)
11. [Troubleshooting](#troubleshooting)
12. [Backup & Restore](#backup--restore)

---

## 1. Arsitektur Sistem

Sistem ini terdiri dari **2 aplikasi** yang berjalan paralel:

```
┌──────────────────────┐      HTTP POST /recognize       ┌─────────────────────┐
│                      │ ──────────────────────────────> │                     │
│  Laravel 12          │                                 │  Flask (Python)     │
│  port: 8000          │ <─────────────────────────────  │  port: 5000         │
│  pelanggaran-siswa   │   JSON response                 │  Fr-lbph            │
│                      │                                 │                     │
│  - Auth (guru/admin)│                                 │  - cv2.face.LBPH    │
│  - CRUD Siswa        │                                 │  - Haar Cascade     │
│  - CRUD Pelanggaran  │                                 │  - VideoCapture     │
│  - Absensi Wajah     │                                 │                     │
└──────────────────────┘                                 └──────────────────��──┘
                                                            │
                                                      dataset/ (foto muka siswa)
                                                      trained_model.xml (model)
```

- **Laravel** menangani autentikasi, manajemen data siswa/pelanggaran, dan UI.
- **Flask** menangani proses vision AI (deteksi & pengenalan wajah) via HTTP API.
- Keduanya berkomunikasi melalui **HTTP REST** di jaringan lokal (`127.0.0.1:5000`).

---

## 2. Persyaratan Sistem

| Komponen       | Minimum                  | Rekomendasi        |
| -------------- | ------------------------ | ------------------ |
| OS             | Windows 10/11 64-bit     | Windows 11 Pro     |
| RAM            | 4 GB                     | 8 GB+              |
| Disk           | 2 GB bebas               | SSD 10 GB+         |
| PHP            | >= 8.2                   | 8.3                |
| Python         | >= 3.10                  | 3.11               |
| Node.js        | >= 18 LTS                | >= 20 LTS          |
| Database       | MySQL 5.7+ / MariaDB 10+ | MySQL 8.0          |

Jika menggunakan **Laragon**, semua dependensi di atas sudah termasuk (PHP, MySQL, Nginx/Apache).

---

## Langkah 1 — Clone dari GitHub

Buka terminal/command prompt, lalu jalankan:

```powershell
cd C:\laragon\www

# A. Clone Aplikasi Laravel (Backend)
git clone https://github.com/aldihidayat35/pelanggaran-siswa.git
cd pelanggaran-siswa
git checkout main   # pastikan branch terbaru

# B. Buka terminal baru (atau window CMD terpisah)
cd C:\laragon\www
git clone https://github.com/aldihidayat35/Fr-lbph.git
cd Fr-lbph
git checkout main
```

Verifikasi hasil clone:

```powershell
# Laravel
dir pelanggaran-siswa
# Harus ada: composer.json, artisan, config/, app/, database/, resources/

# Flask
dir Fr-lbph
# Harus ada: app.py, requirements.txt, run.bat, train.bat, templates/
```

---

## Langkah 2 — Instalasi Backend (Laravel)

### 2.1 Install Composer Dependencies

```powershell
cd C:\laragon\www\pelanggaran-siswa
composer install
```

Jika Composer belum terinstall di sistem: download dari https://getcomposer.org/download/

### 2.2 Copy & Konfigurasi Environment

```powershell
copy .env.example .env
notepad .env
```

Pastikan isi berikut sudah benar:

```ini
APP_NAME="Pelanggaran Siswa"
APP_ENV=local
APP_KEY=base64:/EjJ+bB+rbEFj6d6WCBzX/H9LL/d3T5kV31g+5AE6eI=
APP_DEBUG=true
APP_URL=http://starter-pack.test
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pelanggaran_siswa      # nama database Anda
DB_USERNAME=root
DB_PASSWORD=                       # isi jika MySQL pakai password
FR_LBPH_BASE_URL=http://127.0.0.1:5000
```

**PENTING**: `FR_LBPH_BASE_URL` harus menunjuk ke alamat di mana Flask service berjalan. Jika kedua aplikasi di satu komputer, biarkan `http://127.0.0.1:5000`.

### 2.3 Generate Application Key

```powershell
php artisan key:generate
```

---

## Langkah 3 — Instalasi Frontend (Vite + Tailwind)

```powershell
cd C:\laragon\www\pelanggaran-siswa
npm install
npm run build
```

- `npm install` mengunduh dependensi frontend (axios, vite, tailwind, dll).
- `npm run build` mengompilasi asset (CSS + JS) ke folder `public/build/`.

Untuk development (live reload otomatis):

```powershell
npm run dev
```

Jalankan di **terminal terpisah** sambil server Laravel aktif.

---

## Langkah 4 — Konfigurasi Database

### 4.1 Buat Database

Buka **phpMyAdmin** (http://localhost/phpmyadmin) atau via command line:

```sql
CREATE DATABASE pelanggaran_siswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4.2 Jalankan Migration & Seeder

```powershell
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder
```

Migration akan membuat tabel-tabel:
- `users` (guru & admin)
- `siswas` (data siswa)
- `kategori_pelanggarans`
- `pelanggarans`
- `absensis` (data absensi wajah)
- `app_settings`
- `whatsapp_logs`
- dst.

Seeder akan membuat akun default:
- **Admin**: username `admin`, password `password` (ganti setelah login pertama)

### 4.3 Verifikasi

```powershell
php artisan migrate:status
# Semua migration harus berstatus ["Ran"]
```

---

## Langkah 5 — Instalasi & Jalankan Face Recognition Service (Flask)

### 5.1 Instal Python

Download & install Python >= 3.10 dari https://www.python.org/downloads/

Saat install, centang **"Add Python to PATH"**.

Verifikasi:

```powershell
python --version
# Harus tampil: Python 3.x.x
```

### 5.2 Buat Virtual Environment

```powershell
cd C:\laragon\www\Fr-lbph
python -m venv venv
```

### 5.3 Activate Virtual Environment & Install Packages

```powershell
.\venv\Scripts\activate
pip install -r requirements.txt
```

Packages yang terinstall:
- `Flask` — web framework
- `opencv-python` — deteksi wajah & preprocessing gambar
- `opencv-contrib-python` — LBPHFaceRecognizer
- `numpy` — komputasi numerik

Verifikasi instalasi:

```powershell
python -c "import cv2; print(cv2.__version__)"
# Harus menampilkan versi OpenCV (misal 4.10.0)
```

### 5.4 Jalankan Flask Service

#### Opsi A: Pakai Supervisor (RECOMMENDED — auto-restart)

```powershell
.\run.bat
```

Ini menjalankan supervisor yang akan:
- Restart otomatis jika service crash
- Log ke `logs\service.log`
- Stop jika gagal restart 3x dalam 60 detik

#### Opsi B: Langsung (development)

```powershell
.\venv\Scripts\activate
python app.py
```

Verifikasi service berjalan:

```powershell
curl http://127.0.0.1:5000/health
# Harus return JSON: {"status":"ok","pipeline_version":"v2",...}
```

Service Flask **wajib tetap berjalan** agar fitur face recognition berfungsi. Jangan tutup terminal saat menggunakan opsi B.

---

## Langkah 6 — Integrasi Kedua Aplikasi

Integrasi dilakukan melalui konfigurasi `FR_LBPH_BASE_URL` di file `.env` Laravel.

### 6.1 Konfigurasi di Laravel

Di file `C:\laragon\www\pelanggaran-siswa\.env`:

```ini
FR_LBPH_BASE_URL=http://127.0.0.1:5000
```

Jika Flask service berjalan di mesin lain:

```ini
FR_LBPH_BASE_URL=http://IP_ADDRESS_SERVER_FLASK:5000
```

### 6.2 Verifikasi Integrasi

1. Pastikan Flask berjalan (cek `http://127.0.0.1:5000/health`)
2. Pastikan Laravel berjalan (jalankan `php artisan serve`)
3. Login ke Laravel → masuk ke halaman absensi wajah guru
4. Klik "Mulai Scan" → jika berhasil, canvas akan menampilkan video camera

Jika muncul error "Koneksi Error", cek:
- Flask service masih berjalan
- URL di `.env` benar
- Firewall tidak memblokir port 5000

### 6.3 Setting URL di Database (opsional)

Bisa juga di-set lewat halaman Admin → App Settings:

```
fr_lbph_base_url  →  http://127.0.0.1:5000
```

Setting di database akan menimpa nilai di `.env`.

---

## Langkah 7 — Data Awal & Train Model

### 7.1 Enroll Foto Wajah Siswa

1. Login ke Laravel sebagai **admin**
2. Masuk menu **Siswa**
3. Untuk setiap siswa, klik tombol **"Enroll Face"** atau **"Foto Wajah"**
4. Foto wajah siswa diambil via webcam dan disimpan di `Fr-lbph/dataset/{student_id}/`

### 7.2 Train Model LBPH

Di mesin tempat Flask berjalan:

```powershell
cd C:\laragon\www\Fr-lbph
.\venv\Scripts\activate
python -m pip install -r requirements.txt  # jika belum terinstall
.\train.bat
```

Atau secara langsung:

```powershell
python app.py --train   # jika app.py support flag --train
# Atau:
train.bat
```

Setelah training selesai, file `trained_model.xml` akan tergenerate di `Fr-lbph/trained_model/`.

> **Catatan**: Setiap kali ada siswa baru yang di-enroll, perlu Jalankan ulang `train.bat` untuk melatih ulang model agar mengenali siswa baru.

### 7.3 Verifikasi Akurasi

1. Buka halaman absensi guru
2. Arahkan wajah siswa yang sudah di-enroll ke kamera
3. Sistem akan secara otomatis mendeteksi & mengenali wajah
4. Status "Terkonfirmasi" (lingkaran hijau) = wajah berhasil dikenali
5. Status "Mengumpulkan" (kuning) = butuh beberapa frame stabil

---

## Cara Menjalankan Harian

### Skrip Batch Sederhana (RECOMMENDED)

Buat file `start-all.bat` di `C:\laragon\www\`:

```batch
@echo off
echo ============================================================
echo   Starting Pelanggaran Siswa System
echo ============================================================

REM 1. Start Flask FR Service (supervisor, auto-restart)
start "FR-LBPH Service" cmd /k "cd /d C:\laragon\www\Fr-lbph && venv\Scripts\activate && run.bat"

REM 2. Wait 3 seconds for Flask to initialize
timeout /t 3 /nobreak >nul

REM 3. Start Laravel Development Server
start "Laravel Web App" cmd /k "cd /d C:\laragon\www\pelanggaran-siswa && php artisan serve --host=127.0.0.1 --port=8000"

REM 4. Start Vite Dev Server (live reload)
start "Vite Dev" cmd /k "cd /d C:\laragon\www\pelanggaran-siswa && npm run dev"

echo.
echo Semua service dimulai!
echo - Frontend: http://127.0.0.1:8000
echo - Flask Health: http://127.0.0.1:5000/health
echo.
echo Tekan Ctrl+C di masing-masing window untuk menghentikan service.
pause
```

Jalankan dengan double-click: `start-all.bat`

### Akses Aplikasi

| Layanan             | URL                                |
| ------------------- | ---------------------------------- |
| Aplikasi Web        | http://127.0.0.1:8000              |
| Flask Health Check  | http://127.0.0.1:5000/health       |
| phpMyAdmin          | http://localhost/phpmyadmin        |

### Akun Default

| Role     | Username | Password     |
| -------- | -------- | ------------ |
| Admin    | admin    | password     |
| Guru     | guru     | password     |

> ⚠️ Segera ganti password setelah login pertama!

---

## Troubleshooting

### Masalah: "Model Belum Dilatih"

**Penyebab**: Belum ada model `trained_model.xml` atau belum ada data enrolled.

**Solusi**:
1. Pergi ke Admin → Siswa
2. Enroll foto wajah minimal 1 siswa
3. Jalankan `train.bat` di folder Fr-lbph
4. Refresh halaman absensi

### Masalah: "Koneksi Error" saat Scan

**Penyebab**: Flask service tidak berjalan atau URL salah.

**Solusi**:
1. Buka terminal, cek Flask berjalan: `curl http://127.0.0.1:5000/health`
2. Jika tidak merespons, jalankan ulang Flask: `cd C:\laragon\www\Fr-lbph && .\run.bat`
3. Cek `.env` Laravel → pastikan `FR_LBPH_BASE_URL=http://127.0.0.1:5000`

### Masalah: Camera tidak muncul

**Penyebab**: Browser memblokir akses kamera atau izin tidak diberikan.

**Solusi**:
1. Pastikan URL menggunakan `https` atau `localhost` / `127.0.0.1` (browser modern blok kamera di `http` non-localhost)
2. Buka `chrome://settings/content/camera` → pastikan `127.0.0.1:8000` atau `localhost` diizinkan
3. Coba buka halaman absensi → klik "Pilih Kamera" → izin akses kamera

### Masalah: Build Vite gagal

**Penyebab**: Node.js versi terlalu tua atau dependency korup.

**Solusi**:
```powershell
cd C:\laragon\www\pelanggaran-siswa
rm -r node_modules
rm package-lock.json
npm install
npm run build
```

### Masalah: Migration gagal

**Penyebab**: Database belum dibuat atau koneksi MySQL bermasalah.

**Solusi**:
```sql
-- Buat database
CREATE DATABASE pelanggaran_siswa CHARACTER SET utf8mb4;

-- Jalankan migration
php artisan migrate --force
```

### Masalah: OpenCV / Python error

**Penyebab**: Package belum terinstall atau versi tidak kompatibel.

**Solusi**:
```powershell
cd C:\laragon\www\Fr-lbph
.\venv\Scripts\activate
pip install --upgrade pip
pip install -r requirements.txt
python -c "import cv2; print(cv2.__version__)"
```

### Masalah: Wajah tidak dikenali meskipun sudah enroll

**Penyebab**:
- Model belum di-train ulang setelah enroll baru
- Pencahayaan buruk saat enroll
- Angle wajah saat enroll kurang variatif

**Solusi**:
1. Re-enroll siswa dengan pencahayaan cukup (3-5 pose berbeda: depan, kiri, kanan, atas, bawah)
2. Jalankan ulang `train.bat`
3. Pastikan tidak ada glare/refleksi pada wajah

---

## Backup & Restore

### Backup Database

Via command line:

```powershell
mysqldump -u root -p pelanggaran_siswa > backup-pelanggaran-siswa-$(date:~0,10).sql
```

Via phpMyAdmin:
1. Buka http://localhost/phpmyadmin
2. Pilih database `pelanggaran_siswa`
3. Klik **Export** → **Quick** → **Go**

### Backup Face Recognition Data

Copy keseluruhan folder:
```
C:\laragon\www\Fr-lbph\dataset\       (foto wajah siswa)
C:\laragon\www\Fr-lbph\trained_model\  (model LBPH)
```

### Restore

1. **Database**: import file `.sql` via phpMyAdmin atau:
   ```sql
   CREATE DATABASE pelanggaran_siswa_restore CHARACTER SET utf8mb4;
   USE pelanggaran_siswa_restore;
   SOURCE D:\backup\file-backup.sql;
   ```

2. **Face Data**: Copy kembali folder `dataset/` dan `trained_model.xml` ke lokasi asli.

---

## Ringkasan Urutan Instalasi

```
1. git clone pelanggaran-siswa → composer install → npm install → npm run build
2. Buat database MySQL → php artisan migrate → php artisan db:seed
3. git clone Fr-lbph → python -m venv venv → pip install -r requirements.txt
4. Jalankan Flask: run.bat
5. Jalankan Laravel: php artisan serve
6. Enroll siswa → train.bat
7. Mulai absensi wajah!
```
