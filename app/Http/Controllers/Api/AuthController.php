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

    /**
     * Register User
     *
     * Create a new user account.
     *
     * @group Authentication
     * @unauthenticated
     * @response 201 {
     *   "success": true,
     *   "message": "Registration successful.",
     *   "data": {
     *     "user": { "id": "uuid", "name": "Budi Santoso", "email": "budi@example.com" },
     *     "token": "1|laravel_sanctum_token_string"
     *   }
     * }
     * @response 422 {
     *   "message": "The email has already been taken.",
     *   "errors": { "email": ["The email has already been taken."] }
     * }
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->registerUser($request->validated());
            return $this->successResponse('Registration successful.', $data, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed.', ['detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Login Manual
     *
     * Authenticate using email and password to receive a Bearer token.
     *
     * @group Authentication
     * @unauthenticated
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful.",
     *   "data": {
     *     "user": { "id": "uuid", "name": "Budi Santoso", "email": "budi@example.com" },
     *     "token": "2|laravel_sanctum_token_string"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials.",
     *   "data": []
     * }
     */
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

    /**
     * Login via Google
     *
     * Authenticate using a Google ID token to receive a Bearer token.
     *
     * @group Authentication
     * @unauthenticated
     * @response 200 {
     *   "success": true,
     *   "message": "Google Login successful.",
     *   "data": {
     *     "user": { "id": "uuid", "name": "Budi Santoso", "email": "budi@example.com" },
     *     "token": "3|laravel_sanctum_token_string"
     *   }
     * }
     */
    public function loginWithGoogle(GoogleLoginRequest $request)
    {
        try {
            $data = $this->authService->loginWithGoogle($request->validated()['id_token']);
            return $this->successResponse('Google Login successful.', $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Google Login failed.', ['detail' => $e->getMessage()], 400);
        }
    }

    /**
     * Logout
     *
     * Revoke the current user's active Sanctum Bearer token.
     *
     * @group Authentication
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "message": "Logout successful.",
     *   "data": null
     * }
     */
    public function logout(Request $request)
    {
        // Menghapus token Sanctum milik user yang sedang aktif
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse('Logout successful.');
    }

    /**
     * Get Current User (Me)
     *
     * Retrieve the currently authenticated user's profile.
     *
     * @group Authentication
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "message": "User data retrieved successfully.",
     *   "data": {
     *     "user": { "id": "uuid", "name": "Budi Santoso", "email": "budi@example.com" }
     *   }
     * }
     */
    public function me(Request $request)
    {
        return $this->successResponse('User data retrieved successfully.', ['user' => $request->user()]);
    }
}