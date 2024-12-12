<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    use HasFactory;

    protected $table = 'poli';

    protected $fillable = [
        'nama_poli',
    ];

    /**
     * Relasi ke tabel Dokter (One-to-Many).
     */
    public function dokter()
    {
        return $this->hasMany(Dokter::class, 'id_poli');
    }
}
