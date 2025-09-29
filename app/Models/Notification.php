<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Static methods for creating specific notification types
    public static function createPengajuanIzin($pembimbingId, $peserta, $perizinan)
    {
        return self::create([
            'user_id' => $pembimbingId,
            'type' => 'pengajuan_izin',
            'title' => 'Pengajuan izin baru',
            'message' => "{$peserta->nama_lengkap} mengajukan izin {$perizinan->jenis} untuk tanggal " . Carbon::parse($perizinan->tanggal_mulai ?? $perizinan->tanggal)->format('d M Y'),
            'data' => [
                'perizinan_id' => $perizinan->id,
                'peserta_id' => $peserta->id,
                'jenis' => $perizinan->jenis
            ]
        ]);
    }

    public static function createApprovalIzin($pesertaId, $perizinan, $status)
    {
        $statusText = $status === 'disetujui' ? 'disetujui' : 'ditolak';
        
        return self::create([
            'user_id' => $pesertaId,
            'type' => 'approval_izin',
            'title' => "Pengajuan izin {$statusText}",
            'message' => "Pengajuan izin {$perizinan->jenis} Anda untuk tanggal " . Carbon::parse($perizinan->tanggal_mulai ?? $perizinan->tanggal)->format('d M Y') . " telah {$statusText}",
            'data' => [
                'perizinan_id' => $perizinan->id,
                'status' => $status,
                'jenis' => $perizinan->jenis
            ]
        ]);
    }

    public static function createReminderPresensi($pesertaId, $peserta)
    {
        return self::create([
            'user_id' => $pesertaId,
            'type' => 'reminder_presensi',
            'title' => 'Reminder presensi',
            'message' => 'Jangan lupa melakukan presensi hari ini',
            'data' => [
                'peserta_id' => $peserta->id,
                'date' => now()->format('Y-m-d')
            ]
        ]);
    }

    public static function createPresensiAlert($pembimbingId, $peserta)
    {
        return self::create([
            'user_id' => $pembimbingId,
            'type' => 'presensi_alert',
            'title' => 'Peserta belum presensi',
            'message' => "{$peserta->nama} belum melakukan presensi hari ini",
            'data' => [
                'peserta_id' => $peserta->id,
                'date' => now()->format('Y-m-d')
            ]
        ]);
    }
}
