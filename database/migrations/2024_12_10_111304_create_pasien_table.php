<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255);
            $table->string('password');
            $table->string('alamat', 255)->nullable();
            $table->string('no_ktp', 16)->unique()->nullable();;
            $table->string('no_hp', 15)->nullable();;
            $table->char('no_rm', 10)->unique()->nullable();;
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien');
    }
};
