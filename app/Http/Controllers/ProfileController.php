<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'foto' => 'nullable|image|max:2048',
            'tanggal_lahir' => 'nullable|date|before:today', // ✅ Validasi tanggal lahir
        ]);

        $userId = session('id_pengguna');
        if (!$userId) {
            return back()->with('error', 'Session id_pengguna tidak ditemukan. Silakan login ulang.');
        }

        $user = Pengguna::with(['jabatan', 'pangkatGolongan', 'unitKerja'])->find($userId);
        if (!$user) {
            return back()->with('error', 'Data pengguna tidak ditemukan.');
        }

        // ✅ Update tanggal lahir jika ada
        $tanggalLahirUpdated = false;
        if ($request->filled('tanggal_lahir')) {
            $user->tanggal_lahir = $request->tanggal_lahir;
            $tanggalLahirUpdated = true;
        }

        // 🔹 Path lama tanpa "storage/" (biar mudah hapus di disk)
        $oldPath = $user->foto ? str_replace('storage/', '', $user->foto) : null;

        // === Jika user klik "Hapus Foto" ===
        if ($request->input('hapus_foto') == 1) {
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $user->foto = null;
            $user->save();

            // Reset session ke tanpa foto
            session(['foto' => null]);

            return back()->with('success', 'Foto profil berhasil dihapus.');
        }

        // ✅ Jika hanya update tanggal lahir tanpa foto
        if ($tanggalLahirUpdated && !$request->hasFile('foto')) {
            $user->save();
        }

        // === Upload foto baru ===
        if ($request->hasFile('foto')) {
            // Hapus foto lama dulu
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $file = $request->file('foto');

            // 🔹 Cek ukuran foto (4x3 atau 4x6)
            $imageSize = getimagesize($file);
            $width = $imageSize[0];
            $height = $imageSize[1];

            // Hitung rasio lebar:tinggi
            $ratio = round($width / $height, 2);

            // Rasio ideal 4x3 = 1.33, 4x6 = 0.67
            $isValidRatio = (abs($ratio - 1.33) <= 0.05) || (abs($ratio - 0.67) <= 0.05);

            if (!$isValidRatio) {
                return back()->with('error', 'Ukuran foto harus berasio 4x3 atau 4x6.')->withInput();
            }

            // Simpan foto baru
            $file = $request->file('foto');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('avatars', $filename, 'public');

            $user->foto = 'storage/' . $path;
        }

        // ✅ Simpan perubahan
        $user->save();

        // === Refresh data & perbarui session ===
        $user->refresh();

        session([
            'id_pengguna'   => $user->id_pengguna,
            'nama_lengkap'  => $user->nama_lengkap,
            'nip'           => $user->nip,
            'role'          => $user->role,
            'jabatan'       => $user->jabatan->nama_jabatan ?? '-',
            'pangkat_golongan_ruang' => $user->pangkatGolongan->pangkat ?? '-',
            'unit_kerja'    => $user->unitKerja->nama_unit_kerja ?? '-',
            'masa_kerja'    => $user->masa_kerja ?? '-',
            'foto'          => $user->foto,
            'tanggal_lahir' => $user->tanggal_lahir, // ✅ TAMBAHAN
            'status_kepegawaian' => $user->status_kepegawaian ?? null,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function show()
    {
        $userId = session('id_pengguna');
        $user = Pengguna::with(['jabatan', 'pangkatGolongan', 'unitKerja'])->find($userId);

        if ($user) {
            session([
                'nama_lengkap'  => $user->nama_lengkap,
                'nip'           => $user->nip,
                'jabatan'       => $user->jabatan->nama_jabatan ?? '-',
                'pangkat_golongan_ruang' => $user->pangkatGolongan->pangkat ?? '-',
                'unit_kerja'    => $user->unitKerja->nama_unit_kerja ?? '-',
                'masa_kerja'    => $user->masa_kerja ?? '-',
                'foto'          => $user->foto ?? 'images/default-profile.png',
                'tanggal_lahir' => $user->tanggal_lahir, // ✅ TAMBAHAN
                'status_kepegawaian' => $user->status_kepegawaian ?? null,
            ]);
        }

        return view('pengaturan');
    }
}