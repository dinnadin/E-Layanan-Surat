<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_libur', function (Blueprint $table) {
            // ubah kolom tanggal jadi DATE
            $table->date('tanggal')->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_libur', function (Blueprint $table) {
            // balikin lagi ke varchar(255) kalau rollback
            $table->string('tanggal', 255)->change();
        });
    }
};
