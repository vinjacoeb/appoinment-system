<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal_periksa';

    protected $fillable = [
        'id_dokter',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    /**
     * Relasi ke tabel Dokter (Many-to-One).
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }
}
