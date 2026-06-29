<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    private ?string $pipelineVersion = null;
    private int $pipelineVersionFetchedAt = 0;
    private const PIPELINE_VERSION_TTL = 300; // 5 menit, hindar /health tiap scan

    private function baseUrl(): string
    {
        return rtrim(AppSetting::getValue('fr_lbph_base_url', 'http://127.0.0.1:5000'), '/');
    }

    private function request(string $method, string $path, ?array $payload = null, int $timeout = 25): array
    {
        $endpoint = $this->baseUrl() . '/' . ltrim($path, '/');

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // Aktifkan TCP_NODELAY untuk paket kecil (respons JSON)
        // supaya tidak menunggu Nagle's algorithm sebelum kirim ACK.
        curl_setopt($ch, CURLOPT_TCP_NODELAY, true);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Expect:', // Matikan 100-continue (Laravel dev server) supaya tidak ada RTT ekstra
            ]);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('Face Recognition Service connection failed: ' . $error);
            return [
                'success' => false,
                'recognized' => false,
                'message' => 'Service face recognition tidak terhubung.',
            ];
        }

        $result = json_decode((string) $response, true);
        if (!is_array($result)) {
            Log::error('Face Recognition Service returned invalid JSON: ' . $response);
            return [
                'success' => false,
                'recognized' => false,
                'message' => 'Format respons dari service face recognition tidak valid.',
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            Log::error('Face Recognition Service returned HTTP status ' . $httpCode . ': ' . $response);
            $result['success'] = false;
            $result['recognized'] = false;
            $result['message'] = $result['message'] ?? 'Service face recognition mengembalikan respon error (' . $httpCode . ').';
            $result['http_code'] = $httpCode;
            return $result;
        }

        return $result;
    }

    /**
     * Send a base64 image frame to the FR service and normalize the v2 response.
     * Pipeline version dicache selama PIPELINE_VERSION_TTL detik untuk menghindari
     * HTTP round-trip /health di setiap scan (overhead 50-200ms per call).
     */
    public function scanFace(string $base64Image): array
    {
        $this->fetchPipelineVersion();

        // /recognize: 10s cukup untuk 1 multi-scale + overhead.
        // 25s sebelumnya overkill — kalau tidak selesai 10s, ada masalah di service.
        $result = $this->request('POST', '/recognize', [
            'image' => $base64Image,
        ], 10);

        if (isset($result['top_match']) && is_array($result['top_match'])) {
            $result['student_id'] = $result['top_match']['student_id'] ?? null;
            $result['distance'] = $result['top_match']['distance'] ?? null;
            $result['best_distance'] = $result['top_match']['best_distance'] ?? null;
            $result['distance_std'] = $result['top_match']['distance_std'] ?? null;
            $result['prediction_votes'] = $result['top_match']['votes'] ?? null;
            $result['match_strength'] = $result['top_match']['match_strength'] ?? null;
        }

        return $result;
    }

    public function enrollFace(int $studentId, string $base64Image): array
    {
        return $this->request('POST', '/enroll', [
            'student_id' => $studentId,
            'image' => $base64Image,
        ], 15);
    }

    public function getStudentDatasetStatus(int $studentId): array
    {
        return $this->request('GET', '/dataset/' . $studentId, null, 10);
    }

    public function trainModel(): array
    {
        return $this->request('POST', '/train', [], 120);
    }

    public function health(): array
    {
        return $this->request('GET', '/health', null, 10);
    }

    public function fetchPipelineVersion(): ?string
    {
        // Cache sederhana: pakai timestamp fetch, bukan nullness — sehingga
        // fetch yang gagal (pipelineVersion=null) tidak retry setiap scan.
        $now = time();
        if ($this->pipelineVersionFetchedAt > 0 && ($now - $this->pipelineVersionFetchedAt) < self::PIPELINE_VERSION_TTL) {
            return $this->pipelineVersion;
        }
        try {
            $data = $this->health();
            $this->pipelineVersion = $data['pipeline_version'] ?? null;
            $this->pipelineVersionFetchedAt = $now;
            return $this->pipelineVersion;
        } catch (\Throwable $e) {
            // Catat fetch attempt agar tidak retry setiap scan kalau service down
            $this->pipelineVersionFetchedAt = $now;
            return $this->pipelineVersion;
        }
    }

    public function getPipelineVersion(): ?string
    {
        return $this->pipelineVersion;
    }
}
