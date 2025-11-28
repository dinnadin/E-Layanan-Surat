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
            // Menambahkan kolom tanggal_lahir tanpa posisi spesifik
            $table->date('tanggal_lahir')->nullable();
            
            // Atau jika ingin tidak nullable (wajib diisi)
            // $table->date('tanggal_lahir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('tanggal_lahir');
        });
    }
};