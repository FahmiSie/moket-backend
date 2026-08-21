<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse; // Memanggil standar JSON kita

    protected $authService;

    // Dependency Injection: Controller memanggil Service
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->registerUser($request->validated());
            return $this->successResponse('Registration successful.', $data, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed.', ['detail' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->loginUser($request->validated());
            
            if (!$data) {
                return $this->errorResponse('Invalid credentials.', [], 401);
            }

            return $this->successResponse('Login successful.', $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed.', ['detail' => $e->getMessage()], 500);
        }
    }

    public function loginWithGoogle(GoogleLoginRequest $request)
    {
        try {
            $data = $this->authService->loginWithGoogle($request->validated()['id_token']);
            return $this->successResponse('Google Login successful.', $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Google Login failed.', ['detail' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        // Menghapus token Sanctum milik user yang sedang aktif
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse('Logout successful.');
    }

    public function me(Request $request)
    {
        return $this->successResponse('User data retrieved successfully.', ['user' => $request->user()]);
    }
}