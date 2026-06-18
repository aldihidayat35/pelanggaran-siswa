# Sistem Informasi Pelanggaran Siswa — Design

**Tanggal:** 2026-06-16
**Status:** Draft
**Project:** pelanggaran-siswa (Laravel 12 + Metronic 8)

## 1. Latar Belakang

Project ini melanjutkan project Laravel yang sudah ada dengan Metronic 8. Sistem Informasi Pelanggaran Siswa (SIP) digunakan untuk mencatat pelanggaran siswa. Setiap jenis pelanggaran memiliki poin tertentu. Total poin siswa dihitung otomatis dari akumulasi poin pada tabel riwayat pelanggaran.

**Batasan tahap ini:**
- Tidak ada fitur face recognition / LBPH / AI wajah (akan ditambahkan di tahap berikutnya).
- Fokus pada CRUD master data + pencatatan pelanggaran + dashboard ringkasan.

## 2. Tujuan

1. Admin dapat mengelola master data siswa, kategori pelanggaran, dan jenis pelanggaran.
2. Admin dapat mencatat pelanggaran siswa. Poin pelanggaran disalin otomatis dari master jenis pelanggaran ke tabel riwayat.
3. Total poin siswa dihitung otomatis dari `SUM(poin)` pada tabel `pelanggaran_siswa`.
4. Dashboard menampilkan ringkasan statistik pelanggaran.

## 3. Stack & Pola yang Dipakai

- **Laravel 12** (sudah ada)
- **PHP 8.2+**
- **Eloquent ORM** + **Form Request** untuk validasi
- **Blade** view, Metronic 8 layout (`resources/views/layouts/app.blade.php`)
- **Storage public disk** untuk upload foto siswa & bukti pelanggaran
- **Migration, Model, Controller (resource style)**
- **Pattern existing**: Ikuti pola `Admin\UserController` (CRUD + validasi inline `Request::validate` di controller) — sesuai yang sudah ada di project.

## 4. Struktur Database

### 4.1 Tabel `siswa`
| Field | Tipe | Constraint |
|-------|------|------------|
| id | bigint PK | auto |
| nis | varchar(20) | unique, not null |
| nisn | varchar(20) | unique, nullable |
| nama | varchar(255) | not null |
| jenis_kelamin | enum('Laki-laki','Perempuan') | not null |
| kelas | varchar(50) | not null |
| jurusan | varchar(100) | nullable |
| no_hp_siswa | varchar(20) | nullable |
| nama_orang_tua | varchar(255) | nullable |
| no_hp_orang_tua | varchar(20) | nullable |
| alamat | text | nullable |
| foto | varchar(255) | nullable (path di storage/app/public/siswa) |
| status | enum('Aktif','Tidak Aktif') | default 'Aktif' |
| timestamps | | |

### 4.2 Tabel `kategori_pelanggaran`
| Field | Tipe | Constraint |
|-------|------|------------|
| id | bigint PK | auto |
| nama | varchar(100) | not null |
| deskripsi | text | nullable |
| status | enum('Aktif','Tidak Aktif') | default 'Aktif' |
| timestamps | | |

### 4.3 Tabel `pelanggaran` (jenis pelanggaran)
| Field | Tipe | Constraint |
|-------|------|------------|
| id | bigint PK | auto |
| kode_pelanggaran | varchar(20) | unique, not null |
| nama_pelanggaran | varchar(255) | not null |
| kategori_id | bigint FK | references `kategori_pelanggaran.id` |
| tingkat | enum('Ringan','Sedang','Berat') | not null |
| poin | integer | not null, min:1 |
| deskripsi | text | nullable |
| status | enum('Aktif','Tidak Aktif') | default 'Aktif' |
| timestamps | | |

### 4.4 Tabel `pelanggaran_siswa` (riwayat)
| Field | Tipe | Constraint |
|-------|------|------------|
| id | bigint PK | auto |
| siswa_id | bigint FK | references `siswa.id` |
| pelanggaran_id | bigint FK | references `pelanggaran.id` |
| tanggal_pelanggaran | date | not null |
| poin | integer | not null (snapshot saat insert) |
| catatan | text | nullable |
| bukti | varchar(255) | nullable (path di storage/app/public/bukti) |
| dicatat_oleh | varchar(100) | nullable (default "Admin") |
| status_penanganan | enum('Belum Diproses','Diproses','Selesai') | default 'Belum Diproses' |
| timestamps | | |

## 5. Model & Relasi

### 5.1 `App\Models\Siswa`
- `$fillable`: semua kolom di atas
- `casts`: tidak ada
- Relasi: `hasMany(PelanggaranSiswa)`
- Accessor:
  - `getTotalPoinAttribute()` → return `SUM(poin)` dari relasi `pelanggaranSiswa`
  - `getStatusPembinaanAttribute()` → label & key (Aman / Perhatian / Pembinaan / Panggilan Orang Tua / Rekomendasi Tindakan Khusus) berdasarkan `total_poin`
- Scope: `scopeAktif($q)` → where('status','Aktif')

### 5.2 `App\Models\KategoriPelanggaran`
- `$fillable`: nama, deskripsi, status
- Relasi: `hasMany(Pelanggaran)`

### 5.3 `App\Models\Pelanggaran`
- `$fillable`: semua kolom
- Relasi: `belongsTo(KategoriPelanggaran)`, `hasMany(PelanggaranSiswa)`

### 5.4 `App\Models\PelanggaranSiswa`
- `$fillable`: semua kolom
- Relasi: `belongsTo(Siswa)`, `belongsTo(Pelanggaran)`

## 6. Controller

Semua controller disimpan di `app/Http/Controllers/Admin/PelanggaranSiswa/` (sub-namespace untuk kerapian):

1. `DashboardController` — `index()` untuk dashboard pelanggaran
2. `SiswaController` — CRUD resource + `show()`
3. `KategoriPelanggaranController` — CRUD resource (kecuali show)
4. `PelanggaranController` — CRUD resource + `show()`
5. `PelanggaranSiswaController` — CRUD resource + `show()`

**Validasi:** Pakai `Request::validate()` di controller (sesuai pola `UserController` existing — project ini belum menggunakan Form Request class terpisah).

## 7. Routes

Tambah di `routes/web.php` di dalam group `prefix('pelanggaran-siswa')->name('pelanggaran-siswa.')`:

```php
Route::prefix('pelanggaran-siswa')->name('pelanggaran-siswa.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Siswa (resource)
    Route::resource('siswa', SiswaController::class);

    // Kategori (resource, no show)
    Route::resource('kategori', KategoriPelanggaranController::class)->except(['show']);

    // Jenis Pelanggaran (resource)
    Route::resource('pelanggaran', PelanggaranController::class);

    // Riwayat Pelanggaran Siswa (resource)
    Route::resource('riwayat', PelanggaranSiswaController::class)->parameters([
        'riwayat' => 'pelanggaranSiswa',
    ]);
});
```

Letak: Tetap dalam group `middleware(['auth', 'admin'])` (sudah ada di route file).

## 8. Views (Blade)

Mengikuti pola existing di `resources/views/admin/users/`. Buat folder baru:
- `resources/views/admin/pelanggaran-siswa/dashboard.blade.php`
- `resources/views/admin/pelanggaran-siswa/siswa/{index,create,edit,show}.blade.php`
- `resources/views/admin/pelanggaran-siswa/kategori/{index,create,edit}.blade.php`
- `resources/views/admin/pelanggaran-siswa/pelanggaran/{index,create,edit,show}.blade.php`
- `resources/views/admin/pelanggaran-siswa/riwayat/{index,create,edit,show}.blade.php`

Setiap view:
- `@extends('layouts.app')`
- `@section('title')`, `@section('page-title')`, `@section('breadcrumb')`
- Pakai komponen Metronic 8 (card, table, badge, form-control-solid, dll) persis seperti di `users/*.blade.php`.

## 9. Sidebar Menu

Tambah section baru "Pelanggaran Siswa" di `resources/views/layouts/partials/_menu.blade.php` di bawah "Manajemen" sebelum "Pengaturan", dengan sub-menu:
- Dashboard
- Data Siswa
- Kategori
- Jenis Pelanggaran
- Riwayat Pelanggaran

## 10. Logic Penting

### 10.1 Snapshot Poin (kritikal)
Saat insert ke `pelanggaran_siswa`:
```php
$pelanggaran = Pelanggaran::findOrFail($request->pelanggaran_id);
$data['poin'] = $pelanggaran->poin; // salin dari master
PelanggaranSiswa::create($data);
```
Jika master `pelanggaran.poin` diubah di kemudian hari, riwayat lama **tetap** menyimpan poin lama (snapshot).

### 10.2 Total Poin Dinamis
Tidak ada kolom `total_poin` di tabel `siswa`. Total dihitung via accessor:
```php
public function getTotalPoinAttribute() {
    return (int) $this->pelanggaranSiswa()->sum('poin');
}
```
Pada query besar (mis. ranking), gunakan `withSum('pelanggaranSiswa as total_poin')` untuk efisiensi.

### 10.3 Status Pembinaan (di accessor)
```php
public function getStatusPembinaanAttribute() {
    $poin = $this->total_poin;
    return match(true) {
        $poin <= 25 => ['key'=>'aman', 'label'=>'Aman', 'badge'=>'badge-light-success'],
        $poin <= 50 => ['key'=>'perhatian', 'label'=>'Perhatian', 'badge'=>'badge-light-info'],
        $poin <= 75 => ['key'=>'pembinaan', 'label'=>'Pembinaan', 'badge'=>'badge-light-warning'],
        $poin <= 100 => ['key'=>'panggilan_ortu', 'label'=>'Panggilan Orang Tua', 'badge'=>'badge-light-danger'],
        default => ['key'=>'rekomendasi', 'label'=>'Rekomendasi Tindakan Khusus', 'badge'=>'badge-danger'],
    };
}
```

### 10.4 Validasi Pemilihan Siswa & Pelanggaran Aktif
Form request untuk `PelanggaranSiswaController`:
- `siswa_id` → exists di `siswa` dengan `status = 'Aktif'`
- `pelanggaran_id` → exists di `pelanggaran` dengan `status = 'Aktif'`

## 11. Seeder

`database/seeders/KategoriPelanggaranSeeder.php` — 6 kategori (Kedisiplinan, Kerapian, Kehadiran, Etika, Keamanan, Pelanggaran Berat).

`database/seeders/PelanggaranSeeder.php` — 7 jenis pelanggaran contoh sesuai requirement.

Daftarkan di `DatabaseSeeder.php`.

## 12. File yang Akan Dibuat/Diubah

### Migration (4 file baru)
- `database/migrations/2026_06_16_000001_create_siswa_table.php`
- `database/migrations/2026_06_16_000002_create_kategori_pelanggaran_table.php`
- `database/migrations/2026_06_16_000003_create_pelanggaran_table.php`
- `database/migrations/2026_06_16_000004_create_pelanggaran_siswa_table.php`

### Model (4 file baru)
- `app/Models/Siswa.php`
- `app/Models/KategoriPelanggaran.php`
- `app/Models/Pelanggaran.php`
- `app/Models/PelanggaranSiswa.php`

### Controller (5 file baru)
- `app/Http/Controllers/Admin/PelanggaranSiswa/DashboardController.php`
- `app/Http/Controllers/Admin/PelanggaranSiswa/SiswaController.php`
- `app/Http/Controllers/Admin/PelanggaranSiswa/KategoriPelanggaranController.php`
- `app/Http/Controllers/Admin/PelanggaranSiswa/PelanggaranController.php`
- `app/Http/Controllers/Admin/PelanggaranSiswa/PelanggaranSiswaController.php`

### Views (15 file baru)
Lihat section 8.

### Routes
- `routes/web.php` — tambah group baru

### Sidebar
- `resources/views/layouts/partials/_menu.blade.php` — tambah section

### Seeder (3 file baru/diubah)
- `database/seeders/KategoriPelanggaranSeeder.php` (baru)
- `database/seeders/PelanggaranSeeder.php` (baru)
- `database/seeders/DatabaseSeeder.php` (update)

## 13. URL yang Bisa Diakses

Setelah migrate + seed, dengan login sebagai admin:
- `/pelanggaran-siswa/dashboard`
- `/pelanggaran-siswa/siswa`
- `/pelanggaran-siswa/siswa/create`
- `/pelanggaran-siswa/siswa/{id}`
- `/pelanggaran-siswa/siswa/{id}/edit`
- `/pelanggaran-siswa/kategori`
- `/pelanggaran-siswa/kategori/create`
- `/pelanggaran-siswa/kategori/{id}/edit`
- `/pelanggaran-siswa/pelanggaran`
- `/pelanggaran-siswa/pelanggaran/create`
- `/pelanggaran-siswa/pelanggaran/{id}`
- `/pelanggaran-siswa/pelanggaran/{id}/edit`
- `/pelanggaran-siswa/riwayat`
- `/pelanggaran-siswa/riwayat/create`
- `/pelanggaran-siswa/riwayat/{id}`
- `/pelanggaran-siswa/riwayat/{id}/edit`

## 14. Cara Menjalankan

```bash
cd C:\laragon\www\pelanggaran-siswa
php artisan migrate
php artisan db:seed
php artisan route:list --name=pelanggaran-siswa
php artisan serve
```

## 15. Cakupan & Risiko

- Tidak ada perubahan terhadap layout/partials Metronic yang sudah ada — hanya menambahkan menu di sidebar.
- Tidak mengubah auth, middleware, atau fitur existing.
- Pola controller mengikuti `UserController` (inline validation). Jika user ingin Form Request, bisa ditambahkan di iterasi berikutnya.
- File upload disimpan di `storage/app/public/siswa` dan `storage/app/public/bukti` — perlu `php artisan storage:link` (kemungkinan sudah dilakukan di setup awal).

## 16. Di Luar Scope (Tahap Berikutnya)

- Face recognition / LBPH
- Otentikasi kamera
- ML / AI untuk identifikasi siswa
- Notifikasi real-time (email/WA) ke orang tua
- Export PDF / Excel laporan
- Multi-role yang lebih granular (kepala sekolah, wali kelas, dll)
