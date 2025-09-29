<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';

    protected $fillable = [
        'user_id',
        'nim',
        'nama_lengkap',
        'universitas',
        'jurusan',
        'tanggal_mulai',
        'tanggal_selesai',
        'pembimbing_id',
        'lokasi_id',
        'alamat',
        'no_telepon',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Boot method untuk model events
     */
    protected static function boot()
    {
        parent::boot();

        // Event ketika model di-update
        static::updated(function ($peserta) {
            // Jika tanggal_selesai berubah, refresh status
            if ($peserta->wasChanged('tanggal_selesai')) {
                $peserta->refreshStatus();
            }
        });

        // Event ketika model di-save (create atau update)
        static::saved(function ($peserta) {
            // Pastikan status selalu up-to-date setelah save
            if ($peserta->tanggal_selesai) {
                $peserta->refreshStatus();
            }
        });
    }

    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke pembimbing melalui user_id
     */
    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id')->where('role', 'pembimbing');
    }

    /**
     * Relasi ke pembimbing detail (tabel pembimbing)
     * Menggunakan join manual karena pembimbing_id di peserta merujuk ke users.id
     */
    public function pembimbingDetail()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id', 'user_id');
    }

    /**
     * Relasi ke lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    /**
     * Relasi ke presensi
     */
    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Relasi ke presensi hari ini
     */
    public function presensiHariIni()
    {
        return $this->hasOne(Presensi::class)->whereDate('tanggal', today());
    }

    /**
     * Scope untuk peserta aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Relationship to Kegiatan
     */
    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class, 'peserta_id');
    }

    /**
     * Accessor untuk status yang otomatis berubah berdasarkan periode magang
     */
    public function getStatusAttribute($value)
    {
        // Jika tidak ada tanggal selesai, return status asli
        if (!$this->tanggal_selesai) {
            return $value;
        }

        $now = now();
        $isExpired = $now->gt($this->tanggal_selesai);
        
        // Case 1: Periode sudah lewat tapi status masih aktif -> ubah ke non-aktif
        if ($isExpired && $value === 'aktif') {
            $this->updateQuietly(['status' => 'non-aktif']);
            return 'non-aktif';
        }
        
        // Case 2: Periode diperpanjang (belum lewat) tapi status non-aktif -> ubah ke aktif
        if (!$isExpired && $value === 'non-aktif') {
            // Pastikan periode memang diperpanjang dengan mengecek jika tanggal selesai >= hari ini
            if ($this->tanggal_selesai >= $now->startOfDay()) {
                $this->updateQuietly(['status' => 'aktif']);
                return 'aktif';
            }
        }

        return $value;
    }

    /**
     * Relasi ke pengajuan presensi
     */
    public function pengajuanPresensis()
    {
        return $this->hasMany(PengajuanPresensi::class);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'aktif' => 'Aktif',
            'non-aktif' => 'Non-Aktif',
            default => 'Unknown'
        };
    }

    /**
     * Method untuk memperbarui status berdasarkan periode secara manual
     * Berguna saat update data peserta atau untuk refresh status
     */
    public function refreshStatus()
    {
        $originalStatus = $this->getRawOriginal('status');
        $currentStatus = $this->getStatusAttribute($originalStatus);
        
        // Refresh model agar perubahan terlihat
        $this->refresh();
        
        return $currentStatus;
    }

    /**
     * Static method untuk memperbarui semua status peserta
     * Berguna untuk cron job atau batch update
     */
    public static function updateAllStatuses()
    {
        $updated = 0;
        
        // Update peserta yang statusnya aktif tapi periodenya sudah lewat
        $expiredActive = self::where('status', 'aktif')
            ->where('tanggal_selesai', '<', now())
            ->whereNotNull('tanggal_selesai');
        
        $updated += $expiredActive->update(['status' => 'non-aktif']);
        
        // Update peserta yang statusnya non-aktif tapi periodenya diperpanjang
        $extendedInactive = self::where('status', 'non-aktif')
            ->where('tanggal_selesai', '>=', now()->startOfDay())
            ->whereNotNull('tanggal_selesai');
            
        $updated += $extendedInactive->update(['status' => 'aktif']);
        
        return $updated;
    }
}
