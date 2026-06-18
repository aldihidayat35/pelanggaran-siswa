<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\WhatsAppLog;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     */
    public function sendMessage(string $to, string $message, ?int $siswaId = null, string $type = 'test send'): array
    {
        $apiUrl = AppSetting::getValue('wa_api_url', 'http://jokiin35.space/api');
        $apiToken = AppSetting::getValue('wa_api_token', 'WATOKEN-C82B724CE4762D0D-2026');
        $sessionId = AppSetting::getValue('wa_session_id', 'guest_watoken5c3881bffcc27');
        $statusActive = AppSetting::getValue('wa_status', '1');

        if ($statusActive !== '1') {
            Log::info("WhatsApp API is disabled in settings. Skipping message to {$to}");
            
            WhatsAppLog::create([
                'siswa_id' => $siswaId,
                'to' => $to ?: 'N/A',
                'type' => $type,
                'message' => $message,
                'status' => 'gagal',
                'response' => 'WhatsApp API Status is inactive in settings.',
            ]);

            return [
                'success' => false,
                'message' => 'WhatsApp API is disabled in settings.',
            ];
        }

        if (empty($to)) {
            WhatsAppLog::create([
                'siswa_id' => $siswaId,
                'to' => 'N/A',
                'type' => $type,
                'message' => $message,
                'status' => 'nomor kosong',
                'response' => 'Recipient phone number is empty.',
            ]);

            return [
                'success' => false,
                'message' => 'Recipient phone number is empty.',
            ];
        }

        // Clean phone number format: replace +, space, -, and ensure it starts with country code (e.g. 62)
        $cleanNumber = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }

        $endpoint = rtrim($apiUrl, '/') . '/send-message';
        $payload = [
            'session_id' => $sessionId,
            'to' => $cleanNumber,
            'message' => $message,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $status = 'gagal';
        $responseLog = $response;

        if ($error) {
            $responseLog = "Curl Error: " . $error;
        } elseif ($httpCode === 200) {
            $resDecoded = json_decode($response, true);
            if (isset($resDecoded['success']) && $resDecoded['success'] == true) {
                $status = 'berhasil';
            } elseif (isset($resDecoded['status']) && $resDecoded['status'] === 'success') {
                $status = 'berhasil';
            }
        }

        WhatsAppLog::create([
            'siswa_id' => $siswaId,
            'to' => $cleanNumber,
            'type' => $type,
            'message' => $message,
            'status' => $status,
            'response' => $responseLog,
        ]);

        return [
            'success' => $status === 'berhasil',
            'message' => $status === 'berhasil' ? 'Pesan berhasil dikirim.' : 'Gagal mengirim pesan.',
            'response' => $responseLog,
            'http_code' => $httpCode,
        ];
    }

    /**
     * Check WA API and session health status.
     */
    public function checkHealth(): array
    {
        $apiUrl = AppSetting::getValue('wa_api_url', 'http://jokiin35.space/api');
        $apiToken = AppSetting::getValue('wa_api_token', 'WATOKEN-C82B724CE4762D0D-2026');
        $sessionId = AppSetting::getValue('wa_session_id', 'guest_watoken5c3881bffcc27');

        // 1. Check general api health
        $healthEndpoint = rtrim($apiUrl, '/') . '/health';
        $ch = curl_init($healthEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
        ]);
        $healthResponse = curl_exec($ch);
        $healthHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $healthError = curl_error($ch);
        curl_close($ch);

        $apiStatus = 'offline';
        $apiDetails = '';
        if ($healthError) {
            $apiDetails = "API Server unreachable: " . $healthError;
        } elseif ($healthHttpCode === 200) {
            $resDecoded = json_decode($healthResponse, true);
            if (isset($resDecoded['status']) && $resDecoded['status'] === 'ok') {
                $apiStatus = 'online';
                $apiDetails = "API Server is active and healthy.";
            } else {
                $apiDetails = "API Server responded with: " . $healthResponse;
            }
        } else {
            $apiDetails = "API Server responded with HTTP code: " . $healthHttpCode;
        }

        // 2. Check session status by fetching sessions list
        $sessionsEndpoint = rtrim($apiUrl, '/') . '/sessions';
        $ch = curl_init($sessionsEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
        ]);
        $sessionsResponse = curl_exec($ch);
        $sessionsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $sessionStatus = 'disconnected';
        $sessionDetails = "Session ID '{$sessionId}' not found.";

        if ($sessionsHttpCode === 200 && !empty($sessionsResponse)) {
            $resDecoded = json_decode($sessionsResponse, true);
            if (isset($resDecoded['success']) && $resDecoded['success'] && isset($resDecoded['sessions'])) {
                foreach ($resDecoded['sessions'] as $session) {
                    if (isset($session['id']) && $session['id'] === $sessionId) {
                        $sessionStatus = $session['status'] ?? 'disconnected';
                        $sessionDetails = "Session status: " . strtoupper($sessionStatus) . " (" . ($session['phoneNumber'] ?? 'No number') . " - " . ($session['name'] ?? 'No name') . ")";
                        break;
                    }
                }
            }
        }

        return [
            'success' => $apiStatus === 'online' && $sessionStatus === 'connected',
            'api_status' => $apiStatus,
            'api_details' => $apiDetails,
            'session_status' => $sessionStatus,
            'session_details' => $sessionDetails,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Format the WhatsApp message by replacing placeholders.
     */
    public function formatMessage(string $template, $siswa, $pelanggaranSiswa = null): string
    {
        $totalPoin = $siswa->total_poin;
        
        $placeholders = [
            '{nama_siswa}' => $siswa->nama,
            '{nis}' => $siswa->nis,
            '{kelas}' => $siswa->kelas,
            '{nama_pelanggaran}' => $pelanggaranSiswa ? ($pelanggaranSiswa->pelanggaran->nama_pelanggaran ?? '-') : '-',
            '{poin_pelanggaran}' => $pelanggaranSiswa ? $pelanggaranSiswa->poin : '0',
            '{total_poin}' => $totalPoin,
            '{tanggal_pelanggaran}' => $pelanggaranSiswa && $pelanggaranSiswa->tanggal_pelanggaran ? $pelanggaranSiswa->tanggal_pelanggaran->format('d/m/Y') : now()->format('d/m/Y'),
            '{link_riwayat_laporan}' => route('pelanggaran-siswa.public-laporan', ['token' => $siswa->whatsapp_token ?? '']),
        ];

        return strtr($template, $placeholders);
    }

    /**
     * Send student violation notification message automatically.
     */
    public function sendNotification($pelanggaranSiswa): array
    {
        $siswa = $pelanggaranSiswa->siswa;
        if (!$siswa) {
            return [
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ];
        }

        $to = $siswa->no_hp_orang_tua;
        $template = AppSetting::getValue('wa_violation_template');

        if (empty($template)) {
            return [
                'success' => false,
                'message' => 'Template pesan WhatsApp belum dikonfigurasi.',
            ];
        }

        $message = $this->formatMessage($template, $siswa, $pelanggaranSiswa);

        return $this->sendMessage($to, $message, $siswa->id, 'notifikasi pelanggaran');
    }
}
