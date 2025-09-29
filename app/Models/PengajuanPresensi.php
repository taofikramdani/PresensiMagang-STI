<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PengajuanPresensi extends Model
{
    protected $table = 'pengajuan_presensis';
    
    protected $fillable = [
        'peserta_id',
        'tanggal_presensi',
        'jenis_pengajuan',
        'jam_masuk',
        'jam_keluar',
        'penjelasan',
        'status',
        'keterangan_pembimbing',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
        'approved_at' => 'datetime'
    ];

    protected $appends = [
        'jenis_pengajuan_display',
        'status_display',
        'status_color'
    ];

    // Relasi
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getJenisPengajuanDisplayAttribute(): string
    {
        return match($this->jenis_pengajuan) {
            'lupa_checkout' => 'Lupa Checkout',
            'presensi_keliru' => 'Presensi Keliru',
            default => ucfirst($this->jenis_pengajuan)
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status)
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'disetujui' => 'green',
            'ditolak' => 'red',
            default => 'gray'
        };
    }

    // Scopes
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

    public function scopeForPeserta($query, $pesertaId)
    {
        return $query->where('peserta_id', $pesertaId);
    }

    public function scopeRecentDays($query, $days = 3)
    {
        return $query->where('tanggal_presensi', '>=', Carbon::now()->subDays($days)->toDateString());
    }

    // Helper Methods
    public static function canSubmitForDate(Carbon $date): bool
    {
        $threeDaysAgo = Carbon::now()->subDays(3)->startOfDay();
        $yesterday = Carbon::now()->subDay()->endOfDay();
        
        return $date->between($threeDaysAgo, $yesterday);
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }

    public function approve(User $approver, string $keterangan = null): bool
    {
        $this->update([
            'status' => 'disetujui',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'keterangan_pembimbing' => $keterangan
        ]);

        return true;
    }

    public function reject(User $approver, string $keterangan): bool
    {
        $this->update([
            'status' => 'ditolak',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'keterangan_pembimbing' => $keterangan
        ]);

        return true;
    }
}
