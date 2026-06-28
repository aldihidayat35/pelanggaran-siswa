<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranSiswa extends Model
{
    use HasFactory;

    public const STATUS_PENANGANAN_OPTIONS = [
        'Belum Diproses' => 'Belum Diproses',
        'Diproses'       => 'Diproses',
        'Selesai'        => 'Selesai',
    ];

    protected $table = 'pelanggaran_siswa';

    protected $fillable = [
        'siswa_id',
        'pelanggaran_id',
        'tanggal_pelanggaran',
        'poin',
        'catatan',
        'bukti',
        'dicatat_oleh',
        'dicatat_oleh_user_id',
        'status_penanganan',
    ];

    protected $casts = [
        'tanggal_pelanggaran' => 'date',
        'poin' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pelanggaran(): BelongsTo
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function dicatatOlehUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_user_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_penanganan) {
            'Belum Diproses' => 'badge-light-danger',
            'Diproses' => 'badge-light-warning',
            'Selesai' => 'badge-light-success',
            default => 'badge-light-secondary',
        };
    }
}
