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
            // Jika tanggal_selesai berubah, refresh status otomatis
            if ($peserta->wasChanged('tanggal_selesai')) {
                $peserta->refreshStatus();
            }
        });

        // Event ketika model di-save (create atau update)
        static::saved(function ($peserta) {
            // Pastikan status selalu up-to-date setelah save, kecuali jika status baru saja diubah manual
            if ($peserta->tanggal_selesai && !$peserta->wasChanged('status')) {
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
     * Accessor untuk status - DINONAKTIFKAN untuk memungkinkan perubahan manual
     * Status sekarang mengikuti nilai database tanpa logika otomatis
     */
    public function getStatusAttribute($value)
    {
        // Kembalikan nilai status asli dari database tanpa modifikasi
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
     * Method untuk memperbarui status berdasarkan periode secara otomatis
     * Berguna saat update data peserta atau untuk refresh status
     */
    public function refreshStatus()
    {
        if (!$this->tanggal_selesai) {
            return;
        }

        $today = \Carbon\Carbon::now()->startOfDay();
        $tanggalSelesai = \Carbon\Carbon::parse($this->tanggal_selesai)->startOfDay();
        
        // Tentukan status baru berdasarkan tanggal
        $newStatus = null;
        
        if ($tanggalSelesai->isBefore($today)) {
            // Periode sudah berakhir -> non-aktif
            $newStatus = 'non-aktif';
        } elseif ($tanggalSelesai->isAfter($today) || $tanggalSelesai->isToday()) {
            // Periode masih berlaku -> aktif
            $newStatus = 'aktif';
        }
        
        // Update status jika berbeda dengan status saat ini
        if ($newStatus && $this->status !== $newStatus) {
            $this->updateQuietly(['status' => $newStatus]);
        }
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
