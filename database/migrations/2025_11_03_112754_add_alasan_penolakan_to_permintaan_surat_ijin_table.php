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
        Schema::table('permintaan_surat_ijin', function (Blueprint $table) {
            // Tambah kolom alasan_penolakan setelah kolom status
            $table->text('alasan_penolakan')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_surat_ijin', function (Blueprint $table) {
            // Hapus kolom alasan_penolakan jika rollback
            $table->dropColumn('alasan_penolakan');
        });
    }
};