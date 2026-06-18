<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kelas', 50);
            $table->string('jurusan', 100)->nullable();
            $table->string('no_hp_siswa', 20)->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('no_hp_orang_tua', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->timestamps();

            $table->index('kelas');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
