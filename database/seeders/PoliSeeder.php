<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menambahkan data poli
        DB::table('poli')->insert([
            'nama_poli' => 'Poli Umum',  // Ganti dengan nama poli yang sesuai
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
