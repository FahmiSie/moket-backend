<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
            throw new \Exception("Akun Anda berstatus: {$user->status}.");
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token
        ];
    }
}