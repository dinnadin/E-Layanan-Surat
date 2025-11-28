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
        Schema::create('permintaan_surat', function (Blueprint $table) {
    $table->id('id_permintaan');
    $table->unsignedBigInteger('id_pengguna')->nullable();
    $table->unsignedBigInteger('penandatangan_id')->nullable();
    $table->string('nama', 255)->nullable();
    $table->string('nip', 50)->nullable();
    $table->string('pangkat_golongan_ruang', 200)->nullable();
    $table->string('status_kepegawaian', 255);
    $table->string('jabatan', 200)->nullable();
    $table->enum('status', ['Pending','Disetujui','Ditolak'])->default('Pending');
    $table->timestamp('tanggal_pengajuan')->useCurrent();
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
        Schema::dropIfExists('permintaan_surat');
    }
};
