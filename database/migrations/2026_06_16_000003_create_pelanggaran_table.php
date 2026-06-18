<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggaran', 20)->unique();
            $table->string('nama_pelanggaran');
            $table->foreignId('kategori_id')->constrained('kategori_pelanggaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('tingkat', ['Ringan', 'Sedang', 'Berat']);
            $table->unsignedInteger('poin');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->timestamps();

            $table->index('kategori_id');
            $table->index('tingkat');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
