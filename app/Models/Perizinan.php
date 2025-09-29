<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Perizinan extends Model
{
    use HasFactory;

    protected $fillable = [
        'peserta_id',
        'jenis',
        'tanggal',
        'keterangan',
        'bukti_dokumen',
        'status',
        'pembimbing_id',
        'catatan_pembimbing',
        'tanggal_approval'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_approval' => 'datetime',
    ];

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    // Relasi ke Pembimbing
    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    // Method untuk approval
    public function setujui($pembimbingId, $catatan = null)
    {
        $this->update([
            'status' => 'disetujui',
            'pembimbing_id' => $pembimbingId,
            'catatan_pembimbing' => $catatan,
            'tanggal_approval' => Carbon::now()
        ]);
    }

    // Method untuk menolak
    public function tolak($pembimbingId, $catatan = null)
    {
        $this->update([
            'status' => 'ditolak',
            'pembimbing_id' => $pembimbingId,
            'catatan_pembimbing' => $catatan,
            'tanggal_approval' => Carbon::now()
        ]);
    }

    // Scope untuk filter berdasarkan status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    // Scope untuk filter berdasarkan jenis
    public function scopeIzin($query)
    {
        return $query->where('jenis', 'izin');
    }

    public function scopeSakit($query)
    {
        return $query->where('jenis', 'sakit');
    }

    // Helper methods
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => 'Unknown'
        };
    }

    public function getJenisLabelAttribute()
    {
        return match($this->jenis) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'text-yellow-600',
            'disetujui' => 'text-green-600',
            'ditolak' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    // Helper untuk bukti dokumen
    public function getBuktiDokumenUrlAttribute()
    {
        if ($this->bukti_dokumen) {
            return route('perizinan.file', ['filename' => $this->bukti_dokumen]);
        }
        return null;
    }

    public function hasBuktiDokumen()
    {
        return !empty($this->bukti_dokumen) && file_exists(storage_path('app/public/perizinan/' . $this->bukti_dokumen));
    }

    public function getBuktiDokumenTypeAttribute()
    {
        if ($this->bukti_dokumen) {
            $extension = pathinfo($this->bukti_dokumen, PATHINFO_EXTENSION);
            return strtolower($extension);
        }
        return null;
    }
}