<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatans';

    protected $fillable = [
        'peserta_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'judul',
        'deskripsi',
        'kategori_aktivitas',
        'bukti',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relationship to Peserta
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    /**
     * Relationship to Pembimbing melalui Peserta
     */
    public function pembimbing()
    {
        return $this->hasOneThrough(
            User::class,           // Model yang ingin diakses
            Peserta::class,        // Model perantara
            'id',                  // Foreign key di tabel perantara (peserta.id)
            'id',                  // Foreign key di model target (users.id)
            'peserta_id',          // Local key di model ini (kegiatan.peserta_id)
            'pembimbing_id'        // Local key di model perantara (peserta.pembimbing_id)
        )->where('users.role', 'pembimbing');
    }

    /**
     * Relationship to Pembimbing Detail melalui Peserta
     */
    public function pembimbingDetail()
    {
        return $this->hasOneThrough(
            Pembimbing::class,     // Model yang ingin diakses
            Peserta::class,        // Model perantara
            'id',                  // Foreign key di tabel perantara (peserta.id)
            'user_id',             // Foreign key di model target (pembimbing.user_id)
            'peserta_id',          // Local key di model ini (kegiatan.peserta_id)
            'pembimbing_id'        // Local key di model perantara (peserta.pembimbing_id)
        );
    }

    /**
     * Accessor for formatted date
     */
    public function getFormattedTanggalAttribute()
    {
        return Carbon::parse($this->tanggal)->setTimezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y');
    }

    /**
     * Accessor for formatted time mulai
     */
    public function getFormattedJamMulaiAttribute()
    {
        // Since jam_mulai is stored as TIME field, just format it directly
        return substr($this->jam_mulai, 0, 5); // Get HH:MM from HH:MM:SS
    }

    /**
     * Accessor for formatted time selesai
     */
    public function getFormattedJamSelesaiAttribute()
    {
        if ($this->jam_selesai) {
            return substr($this->jam_selesai, 0, 5); // Get HH:MM from HH:MM:SS
        }
        return null;
    }

    /**
     * Accessor for formatted kategori aktivitas
     */
    public function getFormattedKategoriAktivitasAttribute()
    {
        $categories = [
            'meeting' => 'Meeting',
            'pengerjaan_tugas' => 'Pengerjaan Tugas',
            'dokumentasi' => 'Dokumentasi',
            'laporan' => 'Laporan'
        ];
        
        return $categories[$this->kategori_aktivitas] ?? ucfirst($this->kategori_aktivitas);
    }

    /**
     * Accessor for duration between jam_mulai and jam_selesai
     */
    public function getDurationAttribute()
    {
        if ($this->jam_mulai && $this->jam_selesai) {
            $mulai = Carbon::createFromFormat('H:i:s', $this->jam_mulai);
            $selesai = Carbon::createFromFormat('H:i:s', $this->jam_selesai);
            
            // Handle case where activity crosses midnight
            if ($selesai->lt($mulai)) {
                $selesai->addDay();
            }
            
            $diff = $mulai->diff($selesai);
            return $diff->format('%H:%I');
        }
        return null;
    }

    /**
     * Accessor for bukti file name
     */
    public function getBuktiFileNameAttribute()
    {
        if ($this->bukti) {
            return basename($this->bukti);
        }
        return null;
    }

    /**
     * Accessor for bukti file type
     */
    public function getBuktiFileTypeAttribute()
    {
        if ($this->bukti) {
            $extension = pathinfo($this->bukti, PATHINFO_EXTENSION);
            return strtolower($extension);
        }
        return null;
    }

    /**
     * Check if bukti is an image
     */
    public function getIsBuktiImageAttribute()
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array($this->bukti_file_type, $imageExtensions);
    }

    /**
     * Scope for filtering by month
     */
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('tanggal', $month);
    }

    /**
     * Scope for filtering by peserta
     */
    public function scopeByPeserta($query, $pesertaId)
    {
        return $query->where('peserta_id', $pesertaId);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for filtering by kategori aktivitas
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori_aktivitas', $kategori);
    }

    /**
     * Scope for ordering by latest
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tanggal', 'desc')->orderBy('jam_mulai', 'desc');
    }
}