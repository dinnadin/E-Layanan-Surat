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
            $table->text('tanda_tangan')->nullable()->after('tanggal_lahir');
            // atau jika ingin menyimpan path file gambar:
            // $table->string('tanda_tangan')->nullable()->after('tanggal_lahir');
        });
    }

    /**
     * Down the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan');
        });
    }
};