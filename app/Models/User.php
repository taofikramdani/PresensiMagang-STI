<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Helper methods untuk role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPembimbing(): bool
    {
        return $this->role === 'pembimbing';
    }

    public function isPeserta(): bool
    {
        return $this->role === 'peserta';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'pembimbing' => 'Pembimbing',
            'peserta' => 'Peserta Magang',
            default => 'Unknown'
        };
    }

    /**
     * Relasi ke tabel peserta (untuk user dengan role peserta)
     */
    public function peserta()
    {
        return $this->hasOne(Peserta::class);
    }

    /**
     * Relasi ke tabel pembimbing (untuk user dengan role pembimbing)
     */
    public function pembimbing()
    {
        return $this->hasOne(Pembimbing::class);
    }

    /**
     * Relasi untuk peserta yang dibimbing (user dengan role pembimbing yang membimbing peserta)
     */
    public function pesertaBimbingan()
    {
        return $this->hasManyThrough(Peserta::class, Pembimbing::class, 'user_id', 'pembimbing_id', 'id', 'id');
    }
}
