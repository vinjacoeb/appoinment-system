<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'obat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_obat',
        'kemasan',
        'harga',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Define any attributes to hide during serialization (optional)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // In Obat model
    public function detailPeriksa()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_obat');  // Adjust the foreign key if necessary
    }

}
