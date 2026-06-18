<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pelanggaran_id')->constrained('pelanggaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_pelanggaran');
            $table->unsignedInteger('poin');
            $table->text('catatan')->nullable();
            $table->string('bukti')->nullable();
            $table->string('dicatat_oleh', 100)->nullable();
            $table->enum('status_penanganan', ['Belum Diproses', 'Diproses', 'Selesai'])->default('Belum Diproses');
            $table->timestamps();

            $table->index('siswa_id');
            $table->index('pelanggaran_id');
            $table->index('tanggal_pelanggaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_siswa');
    }
};
