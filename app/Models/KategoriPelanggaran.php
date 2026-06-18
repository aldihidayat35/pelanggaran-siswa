<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'kategori_pelanggaran';

    protected $fillable = [
        'nama',
        'deskripsi',
        'status',
    ];

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'kategori_id');
    }
}
