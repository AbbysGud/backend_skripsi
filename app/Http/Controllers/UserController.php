<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelurahan; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordMail;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'data' => $user,
        ], 200);
    }

    public function updateRFID(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'required|string|unique:users,rfid_tag',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->rfid_tag = $request->rfid_tag;
        $user->save();

        return response()->json([
            'message' => 'RFID berhasil diperbarui.',
            'data' => $user,
        ], 200);
    }

    public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'weight' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'gender' => 'nullable|string|in:male,female',
            'pregnancy_date' => 'nullable|date|after_or_equal:date_of_birth',
            'breastfeeding_date' => 'nullable|date|after_or_equal:pregnancy_date',
            'daily_goal' => 'nullable|numeric|min:0.1',
            'waktu_mulai' => 'nullable|date_format:H:i:s',
            'waktu_selesai' => 'nullable|date_format:H:i:s',
            'frekuensi_notifikasi' => 'nullable|numeric|min:1',
            'id_kelurahan' => 'nullable|exists:kelurahan,id',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->update($request->only([
            'name', 'date_of_birth', 'weight', 'height', 'gender',
            'pregnancy_date', 'breastfeeding_date', 'daily_goal', 'waktu_mulai', 'waktu_selesai',
            'frekuensi_notifikasi',
            'id_kelurahan',
            'device_id'
        ]));

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user,
        ], 200);
    }

    public function getDataUser($id)
    {
        $user = User::with([
            'kelurahan.kecamatan.kota.provinsi'
        ])->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $userData = $user->toArray();
        $userData['daerah'] = null;

        if ($user->kelurahan) {
            $daerah = [
                'id_kelurahan' => $user->kelurahan->id,
                'nama_kelurahan' => $user->kelurahan->nama,
                'kode_kelurahan' => $user->kelurahan->kode_kelurahan,
            ];

            if ($user->kelurahan->kecamatan) {
                $daerah['id_kecamatan'] = $user->kelurahan->kecamatan->id;
                $daerah['nama_kecamatan'] = $user->kelurahan->kecamatan->nama;
                $daerah['kode_kecamatan'] = $user->kelurahan->kecamatan->kode_kecamatan;

                if ($user->kelurahan->kecamatan->kota) {
                    $daerah['id_kota'] = $user->kelurahan->kecamatan->kota->id;
                    $daerah['nama_kota'] = $user->kelurahan->kecamatan->kota->nama;
                    $daerah['kode_kota'] = $user->kelurahan->kecamatan->kota->kode_kota;

                    if ($user->kelurahan->kecamatan->kota->provinsi) {
                        $daerah['id_provinsi'] = $user->kelurahan->kecamatan->kota->provinsi->id;
                        $daerah['nama_provinsi'] = $user->kelurahan->kecamatan->kota->provinsi->nama;
                        $daerah['kode_provinsi'] = $user->kelurahan->kecamatan->kota->provinsi->kode_provinsi;
                    }
                }
            }

            if (isset($daerah['kode_provinsi']) && isset($daerah['kode_kota']) && isset($daerah['kode_kecamatan']) && isset($daerah['kode_kelurahan'])) {
                $daerah['kode_lengkap_wilayah'] = sprintf(
                    "%s.%s.%s.%s",
                    $daerah['kode_provinsi'],
                    $daerah['kode_kota'],
                    $daerah['kode_kecamatan'],
                    $daerah['kode_kelurahan']
                );
            }
            $userData['daerah'] = $daerah;
        }

        return response()->json([
            'message' => 'Data user ditemukan.',
            'data' => $userData,
        ], 200);
    }
    
    public function getUserByRFID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid_tag' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('rfid_tag', $request->rfid_tag)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User dengan RFID tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Data user ditemukan.',
            'data' => $user,
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token = rand(100000, 999999);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // Kirim email dengan token ke pengguna
            Mail::to($request->email)->send(new ResetPasswordMail($token));

            return response()->json(['message' => 'Link reset password telah dikirim ke email Anda.'], 200);
        }

        return response()->json(['error' => 'Email tidak ditemukan.'], 400);
    }

    public function resetPassword(Request $request)
    {
        // Validasi input dari request
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|numeric',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetToken) {
            return response()->json(['message' => 'Kode OTP atau email invalid'], 400);
        }

        if (now()->diffInMinutes($resetToken->created_at) > 15) {
            return response()->json(['message' => 'Kode OTP telah kadaluarsa'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User Tidak Ditemukan'], 404);
        }

        $user->forceFill([
            'password' => bcrypt($request->password),
        ])->save();

        // Hapus OTP setelah berhasil digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully'], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }
}
