# Implementation Plan — Sistem Informasi Pelanggaran Siswa

**Tanggal:** 2026-06-16
**Base reference:** `docs/superpowers/specs/2026-06-16-pelanggaran-siswa-design.md`

## Tahap 0: Setup Struktur Folder

Buat folder untuk controller dan views:
- `app/Http/Controllers/Admin/PelanggaranSiswa/`
- `resources/views/admin/pelanggaran-siswa/siswa/`
- `resources/views/admin/pelanggaran-siswa/kategori/`
- `resources/views/admin/pelanggaran-siswa/pelanggaran/`
- `resources/views/admin/pelanggaran-siswa/riwayat/`

## Tahap 1: Migration (4 file)

1. `database/migrations/2026_06_16_000001_create_siswa_table.php`
2. `database/migrations/2026_06_16_000002_create_kategori_pelanggaran_table.php`
3. `database/migrations/2026_06_16_000003_create_pelanggaran_table.php`
4. `database/migrations/2026_06_16_000004_create_pelanggaran_siswa_table.php`

## Tahap 2: Models (4 file)

1. `app/Models/Siswa.php` + accessor total_poin, status_pembinaan
2. `app/Models/KategoriPelanggaran.php`
3. `app/Models/Pelanggaran.php`
4. `app/Models/PelanggaranSiswa.php`

## Tahap 3: Controllers (5 file)

1. `DashboardController.php`
2. `SiswaController.php`
3. `KategoriPelanggaranController.php`
4. `PelanggaranController.php`
5. `PelanggaranSiswaController.php`

## Tahap 4: Seeders (3 file)

1. `KategoriPelanggaranSeeder.php` (baru)
2. `PelanggaranSeeder.php` (baru)
3. Update `DatabaseSeeder.php`

## Tahap 5: Routes & Sidebar

1. Update `routes/web.php` — tambah group route
2. Update `resources/views/layouts/partials/_menu.blade.php` — tambah section

## Tahap 6: Views (15 file)

Lihat daftar di design doc section 8.

## Tahap 7: Testing & Verifikasi

1. `php artisan migrate:fresh --seed`
2. `php artisan route:list --name=pelanggaran-siswa`
3. Cek semua halaman bisa diakses via login admin
