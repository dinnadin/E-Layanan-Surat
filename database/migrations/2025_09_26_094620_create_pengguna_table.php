<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('pengguna', function (Blueprint $table) {
    $table->id('id_pengguna');
    $table->string('nama_lengkap', 255)->nullable();
    $table->string('username', 50)->nullable();
    $table->string('password', 255)->nullable();
    $table->string('nip', 255)->nullable();
    $table->unsignedBigInteger('id_jabatan')->nullable();
    $table->unsignedBigInteger('id_pangkat_golongan_ruang')->nullable();
    $table->unsignedBigInteger('id_unit_kerja')->nullable();
    $table->string('masa_kerja', 255)->nullable();
    $table->string('role', 255)->nullable();
    $table->string('foto', 255)->nullable();
    $table->date('tanggal_masuk');

    $table->foreign('id_jabatan')->references('id_jabatan')->on('data_jabatan')->cascadeOnDelete()->cascadeOnUpdate();
    $table->foreign('id_unit_kerja')->references('id_unit_kerja')->on('data_unit_kerja')->cascadeOnDelete()->cascadeOnUpdate();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
