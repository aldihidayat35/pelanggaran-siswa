<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggaran extends Model
{
    use HasFactory;

    public const TINGKAT_OPTIONS = [
        'Ringan' => 'Ringan',
        'Sedang' => 'Sedang',
        'Berat'  => 'Berat',
    ];

    protected $table = 'pelanggaran';

    protected $fillable = [
        'kode_pelanggaran',
        'nama_pelanggaran',
        'kategori_id',
        'tingkat',
        'poin',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }

    public function pelanggaranSiswa(): HasMany
    {
        return $this->hasMany(PelanggaranSiswa::class);
    }

    public function getTingkatBadgeAttribute(): string
    {
        return match ($this->tingkat) {
            'Ringan' => 'badge-light-success',
            'Sedang' => 'badge-light-warning',
            'Berat' => 'badge-light-danger',
            default => 'badge-light-secondary',
        };
    }
}
