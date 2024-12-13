<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('email', 255);
            $table->string('password');
            $table->string('alamat', 255)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->unsignedBigInteger('id_poli');
            $table->foreign('id_poli')->references('id')->on('poli')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
