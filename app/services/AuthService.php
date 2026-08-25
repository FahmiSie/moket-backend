<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;`
use Illuminate\Support\Str;
use Google_Client;
use Exception;

class AuthService
{
    public function registerUser(array $data)
    {
        // Pakai DB Transaction: Jika gagal membuat profil, data user batal disimpan (rollback otomatis)
        return DB::transaction(function () use ($data) {
            
            // 1. Buat entitas User dasar
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'] ?? 'user',
                'status'   => 'active',
            ]);

            // 2. Buat profil berdasarkan role (menggunakan DB Facade karena kita belum generate Model-nya)
            if ($user->role === 'user') {
                DB::table('user_profiles')->insert([
                    'id'            => DB::raw('gen_random_uuid()'),
                    'user_id'       => $user->id,
                    'full_name'     => $data['name'],
                    'phone'         => $data['phone'] ?? null,
                    'school_origin' => $data['school_origin'] ?? null,
                    'class_batch'   => $data['class_batch'] ?? null,
                    'category'      => $data['category'] ?? 'internal',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } elseif ($user->role === 'talent') {
                DB::table('talent_profiles')->insert([
                    'id'            => DB::raw('gen_random_uuid()'),
                    'user_id'       => $user->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 3. Generate Token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token
            ];
        });
    }

    public function loginUser(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        // Cek email dan password
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null; // Return null agar Controller tahu login gagal
        }

        // Cek apakah akun aktif
        if ($user->status !== 'active') {
            throw new Exception("Akun Anda berstatus: {$user->status}.");
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token
        ];
    }

    public function loginWithGoogle(string $idToken)
    {
        // 1. Inisialisasi Google Client
        $client = new Google_Client(['client_id' => config('services.google.client_id')]);
        
        // 2. Verifikasi ID Token secara kriptografis ke Google Server
        $payload = $client->verifyIdToken($idToken);

        // Jika tidak valid (signature salah, kadaluarsa, dsb)
        if (!$payload) {
            throw new Exception('Invalid Google ID Token.');
        }

        // 3. SECURITY CHECK: Verifikasi Audience (Aud)
        // Mencegah Confused Deputy Attack (token dari app lain disalahgunakan ke API kita)
        if ($payload['aud'] !== config('services.google.client_id')) {
            throw new Exception('Audience mismatch. Potential hijacker token.');
        }

        // Ekstrak data dari token
        $googleId = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
        // $picture = $payload['picture'] ?? null; // Bisa disimpan jika tabel punya kolom avatar

        // 4. Proses Link/Create menggunakan DB Transaction
        return DB::transaction(function () use ($googleId, $email, $name) {
            
            // Cari user berdasarkan google_id terlebih dahulu
            $user = User::where('google_id', $googleId)->first();

            if (!$user) {
                // Jika google_id belum ada, cari berdasarkan email (Account Linking)
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Update user lama agar ter-link dengan google_id ini
                    $user->update(['google_id' => $googleId]);
                } else {
                    // Jika benar-benar user baru, buat akun baru
                    $user = User::create([
                        'google_id' => $googleId,
                        'name'      => $name,
                        'email'     => $email,
                        'password'  => null, // User SSO tidak punya password
                        'role'      => 'user', // Default role untuk registrasi via SSO
                        'status'    => 'active',
                    ]);

                    // Buat user_profile default
                    DB::table('user_profiles')->insert([
                        'id'            => DB::raw('gen_random_uuid()'),
                        'user_id'       => $user->id,
                        'full_name'     => $name,
                        'category'      => 'internal',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // 5. Cek status akun (banned/inactive)
            if ($user->status !== 'active') {
                throw new Exception("Akun Anda berstatus: {$user->status}.");
            }

            // 6. Generate Token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token
            ];
        });
    }
}
