<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CustomerServiceController;
use App\Http\Controllers\Api\ServicesController;
use App\Http\Controllers\Api\StaffProfileController;
use App\Http\Controllers\Api\StaffServiceController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::middleware('admin')->prefix('services')->group(function () {
        Route::get('/', [ServicesController::class, 'index']);
        Route::post('/',[ServicesController::class,'store']);
        Route::get('{service}', [ServicesController::class, 'show']);
        Route::put('{service}', [ServicesController::class, 'update']);
        Route::delete('{service}', [ServicesController::class, 'destroy']);
    });
    Route::middleware(['staff'])->prefix('staff')->group(function () {
        Route::get('/profile', [StaffProfileController::class, 'show']);
        Route::post('/profile', [StaffProfileController::class, 'store']);
        Route::put('/profile', [StaffProfileController::class, 'update']);
       Route::prefix('services')->group(function () {
        Route::get('/', [StaffServiceController::class, 'index']);
        Route::put('/', [StaffServiceController::class, 'update']);
       });
    });
    Route::prefix('customer')->group(function () {
        Route::get('/services/{service}/staff', [CustomerServiceController::class, 'staff']);
    });
});
