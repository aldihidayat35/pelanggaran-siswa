<?php

namespace Tests\Feature;

use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class GuruReportingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'is_active' => true,
            'password' => Hash::make('password'),
        ], $overrides));
    }

    private function makeSiswa(array $overrides = []): Siswa
    {
        return Siswa::create(array_merge([
            'nis' => fake()->unique()->numerify('24####'),
            'nisn' => fake()->unique()->numerify('00########'),
            'nama' => fake()->name(),
            'jenis_kelamin' => 'Laki-laki',
            'kelas' => 'X',
            'jurusan' => 'RPL',
            'no_hp_siswa' => '08123456789',
            'nama_orang_tua' => 'Orang Tua',
            'no_hp_orang_tua' => '08123456780',
            'alamat' => 'Alamat test',
            'status' => 'Aktif',
        ], $overrides));
    }

    private function makePelanggaran(array $overrides = []): Pelanggaran
    {
        $kategori = KategoriPelanggaran::create([
            'nama' => 'Disiplin',
            'deskripsi' => 'Kategori disiplin',
            'status' => 'Aktif',
        ]);

        return Pelanggaran::create(array_merge([
            'kode_pelanggaran' => fake()->unique()->bothify('P-###'),
            'nama_pelanggaran' => 'Terlambat',
            'kategori_id' => $kategori->id,
            'tingkat' => 'Ringan',
            'poin' => 10,
            'deskripsi' => 'Datang terlambat',
            'status' => 'Aktif',
        ], $overrides));
    }

    public function test_guru_login_redirects_to_scan_page(): void
    {
        $guru = $this->makeUser('guru', ['email' => 'guru-test@example.com']);

        $response = $this->post('/login', [
            'email' => $guru->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('guru.attendance'));
    }

    public function test_non_admin_cannot_open_admin_user_page(): void
    {
        $guru = $this->makeUser('guru');

        $this->actingAs($guru)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_store_from_face_tracks_logged_in_guru_user(): void
    {
        $guru = $this->makeUser('guru', ['name' => 'Guru Piket']);
        $siswa = $this->makeSiswa();
        $pelanggaran = $this->makePelanggaran();

        $wa = Mockery::mock(WhatsAppService::class);
        $wa->shouldReceive('sendNotification')->once();
        $this->app->instance(WhatsAppService::class, $wa);

        $response = $this->actingAs($guru)->postJson(route('guru.pelanggaran-siswa.store-from-face'), [
            'siswa_id' => $siswa->id,
            'pelanggaran_id' => $pelanggaran->id,
            'tanggal_pelanggaran' => now()->toDateString(),
            'catatan' => 'Test pelaporan guru',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('pelanggaran_siswa', [
            'siswa_id' => $siswa->id,
            'pelanggaran_id' => $pelanggaran->id,
            'dicatat_oleh' => 'Guru Piket',
            'dicatat_oleh_user_id' => $guru->id,
        ]);
    }

    public function test_guru_history_only_shows_own_reports(): void
    {
        $guru = $this->makeUser('guru', ['name' => 'Guru Satu']);
        $otherGuru = $this->makeUser('guru', ['name' => 'Guru Dua']);
        $siswaOwn = $this->makeSiswa(['nama' => 'Siswa Milik Guru']);
        $siswaOther = $this->makeSiswa(['nama' => 'Siswa Guru Lain']);
        $pelanggaran = $this->makePelanggaran();

        PelanggaranSiswa::create([
            'siswa_id' => $siswaOwn->id,
            'pelanggaran_id' => $pelanggaran->id,
            'tanggal_pelanggaran' => now()->toDateString(),
            'poin' => $pelanggaran->poin,
            'dicatat_oleh' => $guru->name,
            'dicatat_oleh_user_id' => $guru->id,
            'status_penanganan' => 'Belum Diproses',
        ]);

        PelanggaranSiswa::create([
            'siswa_id' => $siswaOther->id,
            'pelanggaran_id' => $pelanggaran->id,
            'tanggal_pelanggaran' => now()->toDateString(),
            'poin' => $pelanggaran->poin,
            'dicatat_oleh' => $otherGuru->name,
            'dicatat_oleh_user_id' => $otherGuru->id,
            'status_penanganan' => 'Belum Diproses',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.attendance'))
            ->assertOk()
            ->assertSee('Siswa Milik Guru')
            ->assertDontSee('Siswa Guru Lain');
    }

    public function test_admin_history_on_scan_page_shows_all_reports(): void
    {
        $admin = $this->makeUser('admin');
        $guru = $this->makeUser('guru', ['name' => 'Guru Satu']);
        $siswa = $this->makeSiswa(['nama' => 'Siswa Terlapor']);
        $pelanggaran = $this->makePelanggaran();

        PelanggaranSiswa::create([
            'siswa_id' => $siswa->id,
            'pelanggaran_id' => $pelanggaran->id,
            'tanggal_pelanggaran' => now()->toDateString(),
            'poin' => $pelanggaran->poin,
            'dicatat_oleh' => $guru->name,
            'dicatat_oleh_user_id' => $guru->id,
            'status_penanganan' => 'Belum Diproses',
        ]);

        $this->actingAs($admin)
            ->get(route('guru.attendance'))
            ->assertOk()
            ->assertSee('Siswa Terlapor');
    }
}
