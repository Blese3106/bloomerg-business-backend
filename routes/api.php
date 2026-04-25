<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Protected routes (Sanctum token required)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Tasks
    Route::get('/tasks/new',       [TaskController::class, 'getTask']);
    Route::post('/tasks/validate', [TaskController::class, 'validateTask']);

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index']);

    // Withdrawals
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::get('/withdrawals',  [WithdrawalController::class, 'index']);
});