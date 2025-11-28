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
       Schema::create('permintaan_surat_ijin', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_pengguna')->nullable();
    $table->unsignedBigInteger('penandatangan_id')->nullable();
    $table->date('mulai_tanggal')->nullable();
    $table->time('mulai_jam')->nullable();
    $table->time('selesai_jam')->nullable();
    $table->string('jenis_alasan', 50)->nullable();
    $table->string('deskripsi_alasan', 255)->nullable();
    $table->enum('status', ['pending','Disetujui','Ditolak'])->default('pending');
    $table->string('nip', 50)->nullable();
    $table->string('nama_lengkap', 100)->nullable();
    $table->string('jabatan', 100)->nullable();
    $table->string('unit_kerja', 100)->nullable();
    $table->timestamps();

    $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
    $table->foreign('penandatangan_id')->references('id_pengguna')->on('pengguna');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_surat_ijin');
    }
};
