<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 50)->nullable()->after('email');
            $table->string('no_hp', 20)->nullable()->after('avatar');
            $table->string('jabatan', 100)->nullable()->after('no_hp');
            $table->index('role');
        });

        Schema::table('pelanggaran_siswa', function (Blueprint $table) {
            $table->foreignId('dicatat_oleh_user_id')
                ->nullable()
                ->after('dicatat_oleh')
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->index('dicatat_oleh_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggaran_siswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dicatat_oleh_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['nip', 'no_hp', 'jabatan']);
        });
    }
};
