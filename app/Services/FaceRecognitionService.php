<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    /**
     * Send base64 image frame to FR service and get predicted student ID.
     *
     * Response shape (Python service baru, kontrak v2):
     *   {
     *     success: bool,
     *     recognized: bool,
     *     match_level: "strict"|"loose"|null,
     *     top_match: { student_id, distance, match_strength },
     *     candidates: [{ student_id, distance, match_strength }],
     *     message: string
     *   }
     *
     * Service ini menormalisasi ke top-level student_id, distance & match_strength
     * agar controller (FaceRecognitionController::scan) yang baca $res['student_id']
     * tetap kompatibel.
     */
    public function scanFace(string $base64Image): array
    {
        $baseUrl = AppSetting::getValue('fr_lbph_base_url', 'http://127.0.0.1:5000');
        $endpoint = rtrim($baseUrl, '/') . '/recognize';

        $payload = [
            'image' => $base64Image,
        ];

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

        if ($error) {
            Log::error("Face Recognition Service connection failed: " . $error);
            return [
                'success' => false,
                'recognized' => false,
                'message' => 'Service face recognition tidak terhubung.',
            ];
        }

        if ($httpCode !== 200) {
            Log::error("Face Recognition Service returned HTTP status " . $httpCode . ": " . $response);
            return [
                'success' => false,
                'recognized' => false,
                'message' => 'Service face recognition mengembalikan respon error (' . $httpCode . ').',
            ];
        }

        $result = json_decode($response, true);
        if (!$result || !isset($result['success'])) {
            return [
                'success' => false,
                'recognized' => false,
                'message' => 'Format respons dari service face recognition tidak valid.',
            ];
        }

        // Normalisasi: Python service mengirim top_match.{student_id, distance, match_strength}.
        // Controller Laravel baca $res['student_id']. Map agar tetap kompatibel.
        if (isset($result['top_match']) && is_array($result['top_match'])) {
            $result['student_id'] = $result['top_match']['student_id'] ?? null;
            $result['distance'] = $result['top_match']['distance'] ?? null;
            $result['match_strength'] = $result['top_match']['match_strength'] ?? null;
        }

        return $result;
    }
}