<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class FaceRegistrationRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_face_registration_page(): void
    {
        $response = $this->get('/pelanggaran-siswa/siswa/1/face-registration');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_face_registration_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $siswa = Siswa::create([
            'nis' => '1001',
            'nama' => 'Siswa Test',
            'jenis_kelamin' => Siswa::JENIS_KELAMIN_LAKI_LAKI,
            'kelas' => 'X-A',
            'status' => Siswa::STATUS_AKTIF,
        ]);

        $this->mock(FaceRecognitionService::class, function ($mock) {
            $mock->shouldReceive('getStudentDatasetStatus')->once()->andReturn([
                'success' => true,
                'image_count' => 0,
                'registered' => false,
            ]);
            $mock->shouldReceive('health')->once()->andReturn([
                'success' => true,
                'status' => 'active',
                'pipeline_version' => '2.0',
            ]);
        });

        $response = $this->actingAs($admin)
            ->get(route('pelanggaran-siswa.siswa.face-registration', $siswa));

        $response->assertOk();
        $response->assertSee('Daftar Wajah Siswa');
        $response->assertSee('Siswa Test');
    }

    public function test_inactive_student_cannot_capture_face_registration(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $siswa = Siswa::create([
            'nis' => '1002',
            'nama' => 'Siswa Nonaktif',
            'jenis_kelamin' => Siswa::JENIS_KELAMIN_PEREMPUAN,
            'kelas' => 'X-B',
            'status' => Siswa::STATUS_TIDAK_AKTIF,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('pelanggaran-siswa.siswa.face-registration.capture', $siswa), [
                'image' => 'data:image/jpeg;base64,fake',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Pendaftaran wajah hanya dapat dilakukan untuk siswa aktif.',
        ]);
    }

    public function test_siswa_datatable_does_not_fail_when_whatsapp_token_is_empty(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $siswa = Siswa::create([
            'nis' => '1003',
            'nama' => 'Siswa Tanpa Token',
            'jenis_kelamin' => Siswa::JENIS_KELAMIN_LAKI_LAKI,
            'kelas' => 'X-C',
            'status' => Siswa::STATUS_AKTIF,
        ]);
        $siswa->forceFill(['whatsapp_token' => ''])->saveQuietly();

        $response = $this->actingAs($admin)
            ->getJson(route('pelanggaran-siswa.siswa.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJsonPath('draw', 1);
        $response->assertJsonCount(1, 'data');
        $this->assertStringContainsString('Siswa Tanpa Token', $response->json('data.0.siswa'));
    }
}
