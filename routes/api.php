<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\TestAuthorizationController;

// Public Routes (Bisa diakses tanpa login)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'loginWithGoogle']);
});

// Protected Routes (Hanya bisa diakses jika menyertakan Token Sanctum yang valid)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Test endpoint for MOK-11 (Global Role)
    Route::get('/admin-only', function (Request $request) {
        return response()->json(['message' => 'Welcome Super Admin!']);
    })->middleware('role:super_admin');

    // ============================================================
    // TEMPORARY TESTING ROUTES — MOK-11 (Contextual Policy)
    // Hapus setelah domain controller sesungguhnya menggantikan.
    // ============================================================
    Route::prefix('test')->group(function () {
        // Organization
        Route::get('/organizations/{organization}', [TestAuthorizationController::class, 'viewOrganization']);
        Route::put('/organizations/{organization}', [TestAuthorizationController::class, 'updateOrganization']);
        Route::post('/organizations/{organization}/members', [TestAuthorizationController::class, 'manageMembers']);

        // Event
        Route::post('/organizations/{organization}/events', [TestAuthorizationController::class, 'createEvent']);
        Route::get('/events/{event}', [TestAuthorizationController::class, 'viewEvent']);
        Route::put('/events/{event}', [TestAuthorizationController::class, 'updateEvent']);
        Route::delete('/events/{event}', [TestAuthorizationController::class, 'deleteEvent']);
        Route::post('/events/{event}/check-in', [TestAuthorizationController::class, 'checkInEvent']);
        Route::post('/events/{event}/tickets', [TestAuthorizationController::class, 'manageTickets']);
    });
});

