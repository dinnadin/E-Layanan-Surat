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
        Schema::table('permintaan_surat', function (Blueprint $table) {
            // Hapus kolom status_kepegawaian
            $table->dropColumn('status_kepegawaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_surat', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->string('status_kepegawaian')->nullable();
        });
    }
};