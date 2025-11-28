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
       Schema::create('riwayat_surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengguna')->nullable();
            $table->unsignedBigInteger('id_surat_aktif')->nullable();
            $table->unsignedBigInteger('id_surat_ijin')->nullable();
            $table->unsignedBigInteger('id_cuti')->nullable();
            $table->string('keterangan', 255)->nullable();

            // 🔑 Foreign Keys
            $table->foreign('id_pengguna')
                  ->references('id_pengguna')->on('pengguna');

            $table->foreign('id_surat_aktif')
                  ->references('id_surat')->on('surat_aktif');

            $table->foreign('id_surat_ijin')
                  ->references('id_surat')->on('surat_ijin');

            $table->foreign('id_cuti')
                  ->references('id_cuti')->on('pengajuan_cuti')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_surat');
    }
};
