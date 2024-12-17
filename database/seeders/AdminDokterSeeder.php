<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminDokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Menambahkan Dokter
        DB::table('dokter')->insert([
            'nama' => 'dokter',
            'email' => 'dokter@dokter.com',
            'password' => Hash::make('dokter123'), // Ganti dengan password yang aman
            'alamat' => 'Alamat Dokter',
            'no_hp' => '081234567891',
            'id_poli' => 1, // Sesuaikan dengan ID poli yang ada
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pasien')->insert([
            'name' => 'user',
            'email' => 'user@user.com',
            'password' => Hash::make('user1234'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
