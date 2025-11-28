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
       Schema::create('data_cuti', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_pengguna');
    $table->integer('n_2')->default(0);
    $table->integer('n_1')->default(0);
    $table->integer('n')->default(0);
    $table->integer('jumlah')->default(0);
    $table->integer('diambil')->default(0);
    $table->integer('sisa')->default(0);
    $table->timestamps();

    $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_cuti');
    }
};
