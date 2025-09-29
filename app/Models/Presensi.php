<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensis';

    protected $fillable = [
        'peserta_id',
        'tanggal',
        'jam_kerja_id',
        'lokasi_id',
        'jam_masuk',
        'latitude_masuk',
        'longitude_masuk',
        'foto_masuk',
        'keterangan_masuk',
        'jam_keluar',
        'latitude_keluar',
        'longitude_keluar',
        'foto_keluar',
        'keterangan_keluar',
        'status',
        'durasi_kerja',
        'keterlambatan',
        'keterangan',
        'manual_entry',
        'pengajuan_presensi_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_keluar' => 'decimal:8',
        'longitude_keluar' => 'decimal:8',
        'durasi_kerja' => 'integer',
        'keterlambatan' => 'integer',
    ];

    /**
     * Relasi ke peserta
     */
    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    /**
     * Relasi ke jam kerja
     */
    public function jamKerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id');
    }

    /**
     * Relasi ke lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    /**
     * Relasi ke pengajuan presensi
     */
    public function pengajuanPresensi()
    {
        return $this->belongsTo(PengajuanPresensi::class);
    }

    /**
     * Scope untuk presensi hari ini
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Scope untuk presensi berdasarkan peserta
     */
    public function scopePeserta($query, $pesertaId)
    {
        return $query->where('peserta_id', $pesertaId);
    }

    /**
     * Scope untuk presensi berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk presensi yang sudah masuk
     */
    public function scopeSudahMasuk($query)
    {
        return $query->whereNotNull('jam_masuk');
    }

    /**
     * Scope untuk presensi yang sudah keluar
     */
    public function scopeSudahKeluar($query)
    {
        return $query->whereNotNull('jam_keluar');
    }

    /**
     * Check apakah sudah presensi masuk
     */
    public function isSudahMasuk(): bool
    {
        return !is_null($this->jam_masuk);
    }

    /**
     * Check apakah sudah presensi keluar
     */
    public function isSudahKeluar(): bool
    {
        return !is_null($this->jam_keluar);
    }

    /**
     * Check apakah terlambat
     */
    public function isTerlambat(): bool
    {
        return $this->keterlambatan > 0;
    }

    /**
     * Get status dalam bahasa Indonesia
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa' => 'Alpa'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get durasi kerja dalam format jam:menit
     */
    public function getDurasiKerjaFormatted(): string
    {
        if (!$this->durasi_kerja) {
            return '-';
        }

        $jam = intval($this->durasi_kerja / 60);
        $menit = $this->durasi_kerja % 60;

        return sprintf('%02d:%02d', $jam, $menit);
    }

    /**
     * Get keterlambatan dalam format jam:menit
     */
    public function getKeterlambatanFormatted(): string
    {
        if (!$this->keterlambatan) {
            return '-';
        }

        $jam = intval($this->keterlambatan / 60);
        $menit = $this->keterlambatan % 60;

        return sprintf('%02d:%02d', $jam, $menit);
    }

    /**
     * Hitung durasi kerja otomatis
     */
    public function hitungDurasiKerja(): int
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return 0;
        }

        $masuk = Carbon::parse($this->jam_masuk);
        $keluar = Carbon::parse($this->jam_keluar);

        if ($keluar->lt($masuk)) {
            $keluar->addDay();
        }

        return $masuk->diffInMinutes($keluar);
    }

    /**
     * Hitung keterlambatan berdasarkan jam kerja
     */
    public function hitungKeterlambatan(): int
    {
        if (!$this->jam_masuk || !$this->jamKerja) {
            return 0;
        }

        $jamMasukSeharusnya = Carbon::parse($this->jamKerja->jam_masuk);
        $jamMasukAktual = Carbon::parse($this->jam_masuk);

        if ($jamMasukAktual->gt($jamMasukSeharusnya)) {
            return $jamMasukSeharusnya->diffInMinutes($jamMasukAktual);
        }

        return 0;
    }

    /**
     * Tentukan status presensi otomatis
     */
    public function tentukanStatus(): string
    {
        if (!$this->jam_masuk) {
            return 'alpa';
        }

        $keterlambatan = $this->hitungKeterlambatan();
        $toleransi = $this->jamKerja->toleransi_keterlambatan ?? 0;

        if ($keterlambatan > $toleransi) {
            return 'terlambat';
        }

        return 'hadir';
    }

    /**
     * Get koordinat masuk
     */
    public function getKoordinatMasuk(): string
    {
        if (!$this->latitude_masuk || !$this->longitude_masuk) {
            return '-';
        }

        return $this->latitude_masuk . ', ' . $this->longitude_masuk;
    }

    /**
     * Get koordinat keluar
     */
    public function getKoordinatKeluar(): string
    {
        if (!$this->latitude_keluar || !$this->longitude_keluar) {
            return '-';
        }

        return $this->latitude_keluar . ', ' . $this->longitude_keluar;
    }

    /**
     * Get URL foto masuk
     */
    public function getFotoMasukUrl(): ?string
    {
        if (!$this->foto_masuk) {
            return null;
        }

        return asset('storage/' . $this->foto_masuk);
    }

    /**
     * Get URL foto keluar
     */
    public function getFotoKeluarUrl(): ?string
    {
        if (!$this->foto_keluar) {
            return null;
        }

        return asset('storage/' . $this->foto_keluar);
    }

    /**
     * Check if this is a manual entry
     */
    public function isManualEntry(): bool
    {
        return $this->manual_entry;
    }

    /**
     * Check if this presensi came from approved pengajuan
     */
    public function isFromPengajuan(): bool
    {
        return !is_null($this->pengajuan_presensi_id);
    }
}