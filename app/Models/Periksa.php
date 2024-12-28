<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periksa extends Model
{
    use HasFactory;

    protected $table = 'periksa';

    protected $fillable = [
        'id_daftar_poli',
        'tgl_periksa',
        'catatan',
        'biaya_periksa'
    ];

    /**
     * Relasi ke tabel Dokter (Many-to-One).
     */
    public function daftar()
    {
        return $this->belongsTo(Daftar::class, 'id_daftar_poli');
    }
    public function detailPeriksa()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_periksa');  // Adjust the foreign key if necessary
    }
}
