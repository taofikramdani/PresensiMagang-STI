<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HariLibur extends Model
{
    protected $table = 'hari_liburs';
    
    protected $fillable = [
        'nama_libur',
        'tanggal',
        'deskripsi',
        'jenis',
        'warna',
        'is_active'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Scope untuk hari libur aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk hari libur berdasarkan tahun
     */
    public function scopeTahun($query, $tahun)
    {
        return $query->whereYear('tanggal', $tahun);
    }

    /**
     * Check apakah tanggal tertentu adalah hari libur
     */
    public static function isHariLibur($tanggal)
    {
        return self::aktif()
            ->whereDate('tanggal', $tanggal)
            ->exists();
    }

    /**
     * Get hari libur untuk FullCalendar format
     */
    public static function getForCalendar($start = null, $end = null)
    {
        $query = self::aktif();
        
        if ($start) {
            $query->whereDate('tanggal', '>=', $start);
        }
        
        if ($end) {
            $query->whereDate('tanggal', '<=', $end);
        }
        
        return $query->get()->map(function ($libur) {
            return [
                'id' => $libur->id,
                'title' => $libur->nama_libur,
                'start' => $libur->tanggal->format('Y-m-d'),
                'color' => $libur->warna,
                'backgroundColor' => $libur->warna, // Fallback untuk FullCalendar
                'borderColor' => $libur->warna,
                'allDay' => true,
                'extendedProps' => [
                    'description' => $libur->deskripsi,
                    'jenis' => $libur->jenis,
                    'warna' => $libur->warna, // Juga simpan di extendedProps
                ]
            ];
        });
    }

    /**
     * Get display name untuk jenis
     */
    public function getJenisDisplayAttribute()
    {
        return match($this->jenis) {
            'nasional' => 'Hari Libur Nasional',
            'keagamaan' => 'Hari Libur Keagamaan',
            'custom' => 'Hari Libur Custom',
            default => 'Unknown'
        };
    }
}
