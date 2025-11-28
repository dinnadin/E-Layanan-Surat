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
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
    $table->id('id_cuti');
    $table->unsignedBigInteger('id_pengguna')->nullable();
    $table->unsignedBigInteger('penandatangan_id')->nullable();
    $table->unsignedBigInteger('tandatangan_id');
    $table->date('tanggal_mulai')->nullable();
    $table->string('jenis_permohonan', 255);
    $table->date('tanggal_selesai')->nullable();
    $table->string('satuan_lama', 255);
    $table->text('alasan')->nullable();
    $table->string('alamat_cuti', 255);
    $table->string('lama', 255);
    $table->date('tanggal_pengajuan')->useCurrent();
    $table->timestamps();

    $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
    $table->foreign('penandatangan_id')->references('id_pengguna')->on('pengguna');
    $table->foreign('tandatangan_id')->references('id_pengguna')->on('pengguna');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};
