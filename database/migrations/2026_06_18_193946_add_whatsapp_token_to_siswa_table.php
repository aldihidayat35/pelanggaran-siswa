<?php

use App\Models\Siswa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('whatsapp_token', 64)->nullable()->unique()->after('status');
        });

        // Generate tokens for existing students
        foreach (Siswa::all() as $siswa) {
            if (!$siswa->whatsapp_token) {
                $siswa->whatsapp_token = Str::random(40);
                $siswa->save();
            }
        }
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('whatsapp_token');
        });
    }
};
