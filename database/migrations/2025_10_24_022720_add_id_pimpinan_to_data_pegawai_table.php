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
        Schema::table('pengguna', function (Blueprint $table) {
            // Tambahkan kolom id_pimpinan (nullable jika tidak wajib diisi)
            $table->unsignedBigInteger('id_pimpinan')->nullable()->after('id_pengguna');
            
            // Buat foreign key constraint ke tabel data_pimpinan
            $table->foreign('id_pimpinan')
                  ->references('id_pimpinan')
                  ->on('data_pimpinan')
                  ->onDelete('set null')   // Jika pimpinan dihapus, set NULL
                  ->onUpdate('cascade');    // Jika id_pimpinan diupdate, ikut update
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            // Hapus foreign key constraint dulu
            $table->dropForeign(['id_pimpinan']);
            
            // Lalu hapus kolomnya
            $table->dropColumn('id_pimpinan');
        });
    }
};