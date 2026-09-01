<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HomepageController;

// Public Routes (Bisa diakses tanpa login)
Route::get('/events', [EventController::class, 'index']);
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'loginWithGoogle']);
});

Route::prefix('homepage')->group(function () {
    Route::get('/featured-events', [HomepageController::class, 'featuredEvents']);
    Route::get('/sub-organizations', [HomepageController::class, 'subOrganizations']);
    Route::get('/talent-highlights', [HomepageController::class, 'talentHighlights']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{slug}', [EventController::class, 'show']);

// Public Routes
Route::post('/webhooks/midtrans', [\App\Http\Controllers\Api\PaymentWebhookController::class, 'handle']);

// Protected Routes (Hanya bisa diakses jika menyertakan Token Sanctum yang valid)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Transactions
    Route::post('/events/{event:slug}/checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'store']);
    
    // Test endpoint for MOK-11 (Global Role)
    Route::get('/admin-only', function (Request $request) {
        return response()->json(['message' => 'Welcome Super Admin!']);
    })->middleware('role:super_admin');

});

