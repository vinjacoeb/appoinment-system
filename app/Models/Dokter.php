<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Dokter extends Authenticatable
{
    use HasFactory;

    protected $table = 'dokter';

    protected $fillable = [
        'id_poli',
        'nama',
        'email',
        'password',
        'alamat',
        'no_hp',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel Poli (Many-to-One).
     */
    public function poli()
    {
        return $this->belongsTo(Poli::class, 'id_poli');
    }

    /**
     * Relasi ke tabel Jadwal (One-to-Many).
     */
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_dokter');
    }
}
