<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;      // ✅ tambahkan ini
use Illuminate\Support\Facades\Hash;    // ✅ dan ini

class AdminSeeder extends Seeder
{
     public function run(): void
    {
        // Cek apakah admin sudah ada
        $adminExists = DB::table('pengguna')->where('username', 'admin')->exists();

        if (!$adminExists) {
    DB::table('pengguna')->insert([
        'nama_lengkap' => 'admin',
        'username' => 'admin',
        'password' => Hash::make('9999999999'), // password sama dengan nip
        'role' => 'admin',
        'nip' => '9999999999',
    ]);

            echo "✅ Akun admin berhasil dibuat ulang!\n";
        } 
        else {
            echo "ℹ Akun admin sudah ada, tidak dibuat ulang.\n";
}
    }
}