<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'fr_lbph_base_url'],
            [
                'key' => 'fr_lbph_base_url',
                'value' => 'http://127.0.0.1:5000',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Base URL Face Recognition API (LBPH)'
            ]
        );
    }

    public function down(): void
    {
        AppSetting::where('key', 'fr_lbph_base_url')->delete();
    }
};
