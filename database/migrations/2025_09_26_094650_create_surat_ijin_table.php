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
        Schema::create('surat_ijin', function (Blueprint $table) {
    $table->id('id_surat');
    $table->unsignedBigInteger('penandatangan_id')->nullable();
    $table->unsignedBigInteger('id_permintaan');
    $table->string('ttd', 255)->nullable();
    $table->unsignedBigInteger('penerima_id')->nullable();
    $table->text('keterangan')->nullable();
    $table->enum('status', ['Aktif','Nonaktif','Arsip'])->default('Aktif');
    $table->string('file_surat', 255)->nullable();
    $table->timestamps();

    $table->foreign('penandatangan_id')->references('id_pengguna')->on('pengguna')->cascadeOnDelete()->cascadeOnUpdate();
    $table->foreign('penerima_id')->references('id_pengguna')->on('pengguna')->cascadeOnDelete()->cascadeOnUpdate();
    $table->foreign('id_permintaan')->references('id')->on('permintaan_surat_ijin')->cascadeOnDelete()->cascadeOnUpdate();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_ijin');
    }
};
