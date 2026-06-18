<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'wa_api_url',
                'value' => 'http://jokiin35.space/api',
                'type' => 'text',
                'group' => 'whatsapp',
                'label' => 'Base URL API'
            ],
            [
                'key' => 'wa_api_token',
                'value' => 'WATOKEN-C82B724CE4762D0D-2026',
                'type' => 'text',
                'group' => 'whatsapp',
                'label' => 'Token / API Key'
            ],
            [
                'key' => 'wa_session_id',
                'value' => 'guest_watoken5c3881bffcc27',
                'type' => 'text',
                'group' => 'whatsapp',
                'label' => 'Session ID'
            ],
            [
                'key' => 'wa_sender_number',
                'value' => '6287782292990',
                'type' => 'text',
                'group' => 'whatsapp',
                'label' => 'Nomor Default Pengirim (Optional)'
            ],
            [
                'key' => 'wa_status',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'whatsapp',
                'label' => 'Status WhatsApp API'
            ],
            [
                'key' => 'wa_violation_template',
                'value' => 'Yth. Bapak/Ibu Orang Tua/Wali dari {nama_siswa}, kami informasikan bahwa siswa tersebut tercatat melakukan pelanggaran {nama_pelanggaran} pada tanggal {tanggal_pelanggaran} dengan poin {poin_pelanggaran}. Total poin saat ini adalah {total_poin}. Detail riwayat pelanggaran dapat dilihat melalui link berikut: {link_riwayat_laporan}',
                'type' => 'textarea',
                'group' => 'whatsapp',
                'label' => 'Template Pesan Pelanggaran'
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        AppSetting::whereIn('key', [
            'wa_api_url',
            'wa_api_token',
            'wa_session_id',
            'wa_sender_number',
            'wa_status',
            'wa_violation_template'
        ])->delete();
    }
};
