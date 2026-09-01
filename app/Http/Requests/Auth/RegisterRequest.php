<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kembalikan 'true' karena siapa saja (publik) boleh melakukan proses register
        return true; 
    }

    public function rules(): array
    {
        return [
            // Validasi untuk tabel users
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Wajib ada password_confirmation

            // Validasi untuk tabel user_profiles
            'phone'         => ['nullable', 'string', 'max:20'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'class_batch'   => ['nullable', 'string', 'max:100'], // max:100 sesuai ERD mu
            'category'      => ['nullable', 'string', 'in:internal,external'],
        ];
    }
}
