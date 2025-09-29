<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    use HasFactory;

    protected $table = 'pembimbing';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'departemen',
        'alamat',
        'no_telepon',
        'email_kantor',
        'status',
    ];

    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke peserta yang dibimbing
     * pembimbing_id di peserta merujuk ke user_id di pembimbing
     */
    public function peserta()
    {
        return $this->hasMany(Peserta::class, 'pembimbing_id', 'user_id');
    }

    /**
     * Scope untuk pembimbing aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'aktif' => 'Aktif',
            'non_aktif' => 'Non Aktif',
            default => 'Unknown'
        };
    }

    /**
     * Get jumlah peserta bimbingan aktif
     */
    public function getJumlahPesertaAktif(): int
    {
        return $this->pesertaBimbingan()->whereHas('peserta', function($query) {
            $query->where('status', 'aktif');
        })->count();
    }
}
