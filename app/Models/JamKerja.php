<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JamKerja extends Model
{
    use HasFactory;

    protected $table = 'jam_kerja';

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_keluar',
        'hari_kerja',
        'toleransi_keterlambatan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'hari_kerja' => 'array',
        'is_active' => 'boolean',
        'toleransi_keterlambatan' => 'integer',
    ];

    /**
     * Scope untuk jam kerja aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Status display name
     */
    public function getStatusDisplayName(): string
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Durasi kerja dalam jam (desimal)
     */
    public function getDurasiKerja(): float
    {
        $masuk = Carbon::parse(trim($this->jam_masuk));
        $keluar = Carbon::parse(trim($this->jam_keluar));

        if ($keluar->lt($masuk)) {
            $keluar->addDay();
        }

        $menit = $masuk->diffInMinutes($keluar);
        return round($menit / 60, 2);
    }

    /**
     * Hari kerja dalam format readable
     */
    public function getHariKerjaReadable(): string
    {
        $hariMap = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            'minggu' => 'Minggu',
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu'
        ];

        $hariKerja = $this->hari_kerja;
        if (empty($hariKerja) || !is_array($hariKerja)) {
            return '-';
        }

        $hariReadable = array_map(function ($hari) use ($hariMap) {
            return $hariMap[strtolower(trim($hari))] ?? ucfirst(trim($hari));
        }, $hariKerja);

        return implode(', ', $hariReadable);
    }

    /**
     * Alias untuk getHariKerjaReadable (agar kompatibel)
     */
    public function getHariKerjaDisplayName(): string
    {
        return $this->getHariKerjaReadable();
    }

    /**
     * Jam kerja display (tanpa nama shift, format H:i)
     */
    public function getJamKerjaDisplayName(): string
    {
        return Carbon::parse(trim($this->jam_masuk))->format('H:i')
            . ' - '
            . Carbon::parse(trim($this->jam_keluar))->format('H:i');
    }

    /**
     * Jam kerja lengkap (nama shift + hari + jam)
     */
    public function getJamKerjaLengkap(): string
    {
        return $this->nama_shift . ' | ' . $this->getHariKerjaReadable() . ' | ' . $this->getJamKerjaDisplayName();
    }

    public function getTotalJamKerja(): float
    {
        $hariKerja = $this->hari_kerja;
        if (!is_array($hariKerja)) {
            return 0;
        }

        $totalHari = count($hariKerja);
        return round( $this->getDurasiKerja(), 2);
    }

    /**
     * Cek apakah hari tertentu termasuk hari kerja
     */
    public function isHariKerja($hari = null): bool
    {
        if ($hari === null) {
            $hari = strtolower(now()->locale('id')->dayName);
        }

        $hariKerja = $this->hari_kerja;
        if (!is_array($hariKerja)) {
            return false;
        }

        return in_array(strtolower(trim($hari)), array_map('strtolower', $hariKerja));
    }

    /**
     * Scope untuk jam kerja berlaku hari ini
     */
    public function scopeHariIni($query)
    {
        $hariIni = strtolower(now()->locale('id')->dayName);
        return $query->whereJsonContains('hari_kerja', $hariIni);
    }

    /**
     * Relasi ke presensi (jika tabel presensi ada)
     */
    // public function presensi()
    // {
    //     return $this->hasMany(Presensi::class, 'jam_kerja_id');
    // }
}
