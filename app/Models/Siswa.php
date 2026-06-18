<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected static function booted()
    {
        static::creating(function ($siswa) {
            if (empty($siswa->whatsapp_token)) {
                $siswa->whatsapp_token = \Illuminate\Support\Str::random(40);
            }
        });
    }

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'kelas',
        'jurusan',
        'no_hp_siswa',
        'nama_orang_tua',
        'no_hp_orang_tua',
        'alamat',
        'foto',
        'status',
        'whatsapp_token',
    ];

    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_TIDAK_AKTIF = 'Tidak Aktif';

    public const JENIS_KELAMIN_LAKI_LAKI = 'Laki-laki';
    public const JENIS_KELAMIN_PEREMPUAN = 'Perempuan';

    public const STATUS_PEMBINAAN_AMAN = 'Aman';
    public const STATUS_PEMBINAAN_PERHATIAN = 'Perhatian';
    public const STATUS_PEMBINAAN_PEMBINAAN = 'Pembinaan';
    public const STATUS_PEMBINAAN_PANGGILAN_ORTU = 'Panggilan Orang Tua';
    public const STATUS_PEMBINAAN_REKOMENDASI = 'Rekomendasi Tindakan Khusus';

    public static array $statusOptions = [
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_TIDAK_AKTIF => 'Tidak Aktif',
    ];

    public static array $jenisKelaminOptions = [
        self::JENIS_KELAMIN_LAKI_LAKI => 'Laki-laki',
        self::JENIS_KELAMIN_PEREMPUAN => 'Perempuan',
    ];

    public static array $statusPembinaanOptions = [
        self::STATUS_PEMBINAAN_AMAN => 'Aman',
        self::STATUS_PEMBINAAN_PERHATIAN => 'Perhatian',
        self::STATUS_PEMBINAAN_PEMBINAAN => 'Pembinaan',
        self::STATUS_PEMBINAAN_PANGGILAN_ORTU => 'Panggilan Orang Tua',
        self::STATUS_PEMBINAAN_REKOMENDASI => 'Rekomendasi Tindakan Khusus',
    ];

    public function pelanggaranSiswa(): HasMany
    {
        return $this->hasMany(PelanggaranSiswa::class);
    }

    public function getTotalPoinAttribute(): int
    {
        if (array_key_exists('pelanggaran_siswa_sum_poin', $this->attributes)) {
            return (int) ($this->attributes['pelanggaran_siswa_sum_poin'] ?? 0);
        }

        return (int) $this->pelanggaranSiswa()->sum('poin');
    }

    public function getStatusPembinaanAttribute(): array
    {
        $poin = $this->total_poin;

        return match (true) {
            $poin <= 25 => [
                'key' => 'aman',
                'label' => 'Aman',
                'badge' => 'badge-light-success',
            ],
            $poin <= 50 => [
                'key' => 'perhatian',
                'label' => 'Perhatian',
                'badge' => 'badge-light-info',
            ],
            $poin <= 75 => [
                'key' => 'pembinaan',
                'label' => 'Pembinaan',
                'badge' => 'badge-light-warning',
            ],
            $poin <= 100 => [
                'key' => 'panggilan_ortu',
                'label' => 'Panggilan Orang Tua',
                'badge' => 'badge-light-danger',
            ],
            default => [
                'key' => 'rekomendasi',
                'label' => 'Rekomendasi Tindakan Khusus',
                'badge' => 'badge-danger',
            ],
        };
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }
}
