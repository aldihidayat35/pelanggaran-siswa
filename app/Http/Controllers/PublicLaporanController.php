<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\PelanggaranSiswa;
use Illuminate\Http\Request;

class PublicLaporanController extends Controller
{
    public function show(string $token)
    {
        $siswa = Siswa::where('whatsapp_token', $token)->firstOrFail();
        
        $riwayat = PelanggaranSiswa::with(['pelanggaran.kategori'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->get();
            
        return view('public.laporan.show', compact('siswa', 'riwayat'));
    }
}
