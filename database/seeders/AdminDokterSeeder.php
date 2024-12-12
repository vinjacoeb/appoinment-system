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
        // Menambahkan Admin
        DB::table('dokter')->insert([
            'nama' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'), // Ganti dengan password yang aman
            'alamat' => 'Alamat Admin',
            'no_hp' => '081234567890',
            'id_poli' => 1, // Sesuaikan dengan ID poli yang ada
            'role' => 0, // 0 untuk admin
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Menambahkan Dokter
        DB::table('dokter')->insert([
            'nama' => 'valen',
            'email' => 'valen@dokter.com',
            'password' => Hash::make('dokter123'), // Ganti dengan password yang aman
            'alamat' => 'Alamat Dokter',
            'no_hp' => '081234567891',
            'id_poli' => 1, // Sesuaikan dengan ID poli yang ada
            'role' => 1, // 1 untuk dokter
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
