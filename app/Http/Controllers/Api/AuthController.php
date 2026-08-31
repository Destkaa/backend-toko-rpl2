<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),  // password WAJIB di-hash
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Registrasi berhasil.',
                'data'    => $user,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
        public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (! $user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            if (! Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Password salah.',
                ], 401);
            }

            // Kalau lolos semua pengecekan, buat token baru untuk user ini
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'Login berhasil.',
                'token'   => $token,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
        public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'status'  => true,
                'message' => 'Data profile berhasil diambil.',
                'data'    => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['status' => true, 'message' => 'Logout berhasil.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}