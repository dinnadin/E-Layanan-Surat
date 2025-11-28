<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->enum('status_aktif', ['aktif', 'non-aktif', 'pensiun'])->default('aktif')->after('password');
            $table->date('tanggal_pensiun')->nullable()->after('status_aktif');
            $table->string('keterangan_non_aktif', 255)->nullable()->after('tanggal_pensiun');
        });
    }

    public function down()
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['status_aktif', 'tanggal_pensiun', 'keterangan_non_aktif']);
        });
    }
};