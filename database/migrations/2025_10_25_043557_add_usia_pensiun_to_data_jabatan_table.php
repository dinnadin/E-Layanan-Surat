<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_jabatan', function (Blueprint $table) {
            $table->integer('usia_pensiun')->default(58)->after('nama_jabatan')->comment('Usia pensiun untuk jabatan ini');
        });
    }

    public function down()
    {
        Schema::table('data_jabatan', function (Blueprint $table) {
            $table->dropColumn('usia_pensiun');
        });
    }
};