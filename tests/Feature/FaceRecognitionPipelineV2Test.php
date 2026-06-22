<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Services\FaceRecognitionService;
use Mockery;
use Tests\TestCase;

/**
 * Validasi kontrak respons Python Face Recognition pipeline v2.
 *
 * Pipeline Python return (raw, lewat HTTP):
 * {
 *   "success": true,
 *   "recognized": true,
 *   "top_match": {
 *     "student_id": 100,
 *     "distance": 0.0662,
 *     "match_strength": 0.9993
 *   },
 *   "candidates": [...],
 *   "match_level": "strict"
 * }
 *
 * Service Laravel (FaceRecognitionService::scanFace) menormalisasi
 * top_match.{student_id, distance, match_strength} ke top-level
 * (lihat Task 14 — refactor service field naming).
 *
 * Strategi test:
 *  1. Jalankan PHP built-in HTTP server sebagai fake Python service
 *     di port lokal (di luar 5000 agar tidak konflik).
 *  2. Mock AppSetting::getValue via Mockery alias mock agar service
 *     mengarah ke fake server. Pendekatan ini menghindari ketergantungan
 *     pada driver PDO sqlite (tidak tersedia di env ini).
 *  3. Test:
 *     - cURL request/response nyata
 *     - JSON decoding nyata
 *     - Normalisasi top_match → top-level (Task 14)
 *     - Handling error (HTTP 500, JSON invalid, not recognized)
 *     - Field 'confidence' TIDAK ada di response
 */
class FaceRecognitionPipelineV2Test extends TestCase
{
    private const FAKE_PORT = 5099;

    /**
     * Resource handle untuk PHP built-in server subprocess.
     */
    private static $serverProcess = null;

    /**
     * Direktori docroot untuk fake server.
     */
    private static ?string $fakeDocroot = null;

    /**
     * Path lengkap ke router.php — di-rewrite tiap test
     * untuk ganti mode response tanpa restart server.
     */
    private static ?string $routerPath = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // 1. Buat docroot temporer + router script.
        $docroot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_fake_server_' . uniqid('', true);
        if (!mkdir($docroot, 0777, true) && !is_dir($docroot)) {
            throw new \RuntimeException("Cannot create fake server docroot: $docroot");
        }
        self::$fakeDocroot = $docroot;

        $router = $docroot . DIRECTORY_SEPARATOR . 'router.php';
        $routerSource = <<<'PHP'
<?php
$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$mode = $payload['_fake_response'] ?? 'recognized';

header('Content-Type: application/json');

if ($mode === 'not_recognized') {
    echo json_encode([
        'success' => true,
        'recognized' => false,
        'top_match' => null,
        'candidates' => [],
        'match_level' => 'strict',
        'message' => 'No face recognized',
    ]);
    return;
}

if ($mode === 'error_500') {
    http_response_code(500);
    echo json_encode(['error' => 'internal error']);
    return;
}

if ($mode === 'invalid_json') {
    echo 'this is not json';
    return;
}

// Default: recognized, kontrak v2 (distance + match_strength, TANPA confidence)
echo json_encode([
    'success' => true,
    'recognized' => true,
    'top_match' => [
        'student_id' => 100,
        'distance' => 0.0662,
        'match_strength' => 0.9993,
    ],
    'candidates' => [
        ['student_id' => 100, 'distance' => 0.0662, 'match_strength' => 0.9993],
        ['student_id' => 101, 'distance' => 0.1820, 'match_strength' => 0.9810],
    ],
    'match_level' => 'strict',
    'face_size' => [200, 200],
    'message' => 'Recognized',
]);
PHP;
        file_put_contents($router, $routerSource);
        self::$routerPath = $router;

        // 2. Jalankan PHP built-in server sebagai subprocess.
        $php = PHP_BINARY;
        $cmd = [
            $php,
            '-S', '127.0.0.1:' . self::FAKE_PORT,
            '-t', $docroot,
            $router,
        ];

        $descriptors = [
            0 => ['file', 'NUL', 'r'],
            1 => ['file', 'NUL', 'w'],
            2 => ['file', 'NUL', 'w'],
        ];

        self::$serverProcess = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource(self::$serverProcess)) {
            throw new \RuntimeException("Cannot start fake FR server");
        }

        // 3. Tunggu server siap (max 5 detik).
        $ready = false;
        $deadline = microtime(true) + 5.0;
        while (microtime(true) < $deadline) {
            $sock = @fsockopen('127.0.0.1', self::FAKE_PORT, $errno, $errstr, 0.2);
            if ($sock) {
                fclose($sock);
                $ready = true;
                break;
            }
            usleep(100_000);
        }

        if (!$ready) {
            self::stopServer();
            throw new \RuntimeException("Fake FR server failed to start on port " . self::FAKE_PORT);
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::stopServer();

        if (self::$fakeDocroot !== null && is_dir(self::$fakeDocroot)) {
            foreach (glob(self::$fakeDocroot . '/*') as $f) {
                @unlink($f);
            }
            @rmdir(self::$fakeDocroot);
            self::$fakeDocroot = null;
        }

        parent::tearDownAfterClass();
    }

    private static function stopServer(): void
    {
        if (is_resource(self::$serverProcess)) {
            if (DIRECTORY_SEPARATOR === '\\') {
                $status = proc_get_status(self::$serverProcess);
                if (!empty($status['pid'])) {
                    @exec('taskkill /F /T /PID ' . (int) $status['pid'] . ' 2>NUL');
                }
            } else {
                proc_terminate(self::$serverProcess, 1);
            }
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock AppSetting::getValue untuk return fake server URL.
        // Tidak butuh DB — static method di-stub via Mockery alias.
        $mock = Mockery::mock('alias:' . AppSetting::class);
        $mock->shouldReceive('getValue')
            ->with('fr_lbph_base_url', Mockery::any())
            ->andReturn('http://127.0.0.1:' . self::FAKE_PORT);
        // Fallback untuk key lain (jika ada).
        $mock->shouldReceive('getValue')->andReturn(null);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Encode mode ke dalam base64 (payload yang dikirim ke fake server).
     * Router script decode JSON dan pakai field `_fake_response` untuk pilih response.
     */
    private function buildPayload(string $mode): string
    {
        return base64_encode(json_encode(['_fake_response' => $mode]));
    }

    /**
     * Override mode response di router.php tanpa restart server.
     * Server baca ulang script tiap request, jadi rewrite di file langsung efektif.
     */
    private function setFakeMode(string $mode): void
    {
        // Mode recognized + invalid_json + error_500 semuanya hard-coded —
        // supaya tidak bergantung pada payload.
        $routerSource = <<<PHP
<?php
header('Content-Type: application/json');

if ('$mode' === 'not_recognized') {
    echo json_encode([
        'success' => true,
        'recognized' => false,
        'top_match' => null,
        'candidates' => [],
        'match_level' => 'strict',
        'message' => 'No face recognized',
    ]);
    return;
}

if ('$mode' === 'error_500') {
    http_response_code(500);
    echo json_encode(['error' => 'internal error']);
    return;
}

if ('$mode' === 'invalid_json') {
    echo 'this is not json';
    return;
}

echo json_encode([
    'success' => true,
    'recognized' => true,
    'top_match' => [
        'student_id' => 100,
        'distance' => 0.0662,
        'match_strength' => 0.9993,
    ],
    'candidates' => [
        ['student_id' => 100, 'distance' => 0.0662, 'match_strength' => 0.9993],
        ['student_id' => 101, 'distance' => 0.1820, 'match_strength' => 0.9810],
    ],
    'match_level' => 'strict',
    'face_size' => [200, 200],
    'message' => 'Recognized',
]);
PHP;
        file_put_contents(self::$routerPath, $routerSource);
        // Beri sedikit waktu agar filesystem sinkron.
        usleep(50_000);
    }

    private function scanWithFake(string $mode): array
    {
        $this->setFakeMode($mode);
        $service = app(FaceRecognitionService::class);
        return $service->scanFace('fake-image-base64');
    }

    public function test_face_recognition_response_uses_v2_contract(): void
    {
        $result = $this->scanWithFake('recognized');

        // Sukses & recognized
        $this->assertTrue($result['success']);
        $this->assertTrue($result['recognized']);

        // Field hasil normalisasi dari top_match (kontrak v2)
        $this->assertSame(100, $result['student_id']);
        $this->assertSame(0.0662, $result['distance']);
        $this->assertSame(0.9993, $result['match_strength']);

        // Field 'confidence' (kontrak lama) harus TIDAK ada
        $this->assertArrayNotHasKey('confidence', $result);

        // Field tambahan dari Python service tetap diteruskan
        $this->assertSame('strict', $result['match_level']);
        $this->assertIsArray($result['candidates']);
        $this->assertCount(2, $result['candidates']);
        $this->assertSame(100, $result['candidates'][0]['student_id']);
        $this->assertSame(0.0662, $result['candidates'][0]['distance']);
        $this->assertSame(0.9993, $result['candidates'][0]['match_strength']);

        // match_strength dalam range [0, 1]
        $this->assertGreaterThanOrEqual(0, $result['match_strength']);
        $this->assertLessThanOrEqual(1, $result['match_strength']);

        // distance tidak negatif
        $this->assertGreaterThanOrEqual(0, $result['distance']);
    }

    public function test_face_recognition_normalizes_top_match_to_top_level(): void
    {
        $result = $this->scanWithFake('recognized');

        // student_id, distance, match_strength HARUS ada di top-level
        $this->assertArrayHasKey('student_id', $result);
        $this->assertArrayHasKey('distance', $result);
        $this->assertArrayHasKey('match_strength', $result);

        // top_match asli dari Python juga masih ada
        $this->assertArrayHasKey('top_match', $result);
        $this->assertSame(100, $result['top_match']['student_id']);
        $this->assertSame(0.0662, $result['top_match']['distance']);
        $this->assertSame(0.9993, $result['top_match']['match_strength']);
    }

    public function test_face_recognition_not_recognized_returns_no_top_match(): void
    {
        $result = $this->scanWithFake('not_recognized');

        $this->assertTrue($result['success']);
        $this->assertFalse($result['recognized']);

        // top_match null → student_id/distance/match_strength null
        $this->assertNull($result['student_id'] ?? null);
        $this->assertNull($result['distance'] ?? null);
        $this->assertNull($result['match_strength'] ?? null);
    }

    public function test_face_recognition_handles_http_500(): void
    {
        $result = $this->scanWithFake('error_500');

        $this->assertFalse($result['success']);
        $this->assertFalse($result['recognized']);
        $this->assertStringContainsString('500', $result['message']);
    }

    public function test_face_recognition_handles_invalid_json(): void
    {
        $result = $this->scanWithFake('invalid_json');

        $this->assertFalse($result['success']);
        $this->assertFalse($result['recognized']);
        $this->assertStringContainsString('Format respons', $result['message']);
    }
}
