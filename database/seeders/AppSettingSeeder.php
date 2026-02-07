<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'Starter Pack', 'type' => 'text', 'group' => 'general', 'label' => 'Nama Aplikasi'],
            ['key' => 'app_description', 'value' => 'Sistem Manajemen Aplikasi', 'type' => 'textarea', 'group' => 'general', 'label' => 'Deskripsi Aplikasi'],
            ['key' => 'app_version', 'value' => 'v1.0.0', 'type' => 'text', 'group' => 'general', 'label' => 'Versi Aplikasi'],
            ['key' => 'app_logo', 'value' => null, 'type' => 'image', 'group' => 'general', 'label' => 'Logo Aplikasi'],
            ['key' => 'app_logo_dark', 'value' => null, 'type' => 'image', 'group' => 'general', 'label' => 'Logo Aplikasi (Dark)'],
            ['key' => 'favicon', 'value' => null, 'type' => 'image', 'group' => 'general', 'label' => 'Favicon'],

            // Contact
            ['key' => 'contact_email', 'value' => 'admin@example.com', 'type' => 'text', 'group' => 'kontak', 'label' => 'Email Kontak'],
            ['key' => 'contact_phone', 'value' => '+62 812 3456 7890', 'type' => 'text', 'group' => 'kontak', 'label' => 'Nomor Telepon'],
            ['key' => 'contact_address', 'value' => 'Jl. Contoh No. 123, Jakarta, Indonesia', 'type' => 'textarea', 'group' => 'kontak', 'label' => 'Alamat'],

            // Social Media
            ['key' => 'social_facebook', 'value' => '', 'type' => 'text', 'group' => 'sosial_media', 'label' => 'Facebook URL'],
            ['key' => 'social_instagram', 'value' => '', 'type' => 'text', 'group' => 'sosial_media', 'label' => 'Instagram URL'],
            ['key' => 'social_twitter', 'value' => '', 'type' => 'text', 'group' => 'sosial_media', 'label' => 'Twitter / X URL'],
            ['key' => 'social_youtube', 'value' => '', 'type' => 'text', 'group' => 'sosial_media', 'label' => 'YouTube URL'],

            // SEO
            ['key' => 'meta_title', 'value' => 'Starter Pack - Laravel Admin', 'type' => 'text', 'group' => 'seo', 'label' => 'Meta Title'],
            ['key' => 'meta_description', 'value' => 'Starter pack aplikasi admin berbasis Laravel dengan template Metronic', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Meta Description'],
            ['key' => 'meta_keywords', 'value' => 'laravel, admin, starter pack', 'type' => 'text', 'group' => 'seo', 'label' => 'Meta Keywords'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
