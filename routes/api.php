<?php

use App\Http\Controllers\Api\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\ServicesController;
use App\Http\Controllers\Api\Admin\StaffApplicationController as AdminStaffApplicationController;
use App\Http\Controllers\Api\Admin\StaffController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Api\Customer\AvailabilityController;
use App\Http\Controllers\Api\Customer\CustomerServiceController;
use App\Http\Controllers\Api\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Api\Staff\AppointmentController as StaffAppointmentController;
use App\Http\Controllers\Api\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Api\Staff\DesignationController;
use App\Http\Controllers\Api\Staff\StaffApplicationController;
use App\Http\Controllers\Api\Staff\StaffProfileController;
use App\Http\Controllers\Api\Staff\StaffServiceController;
use App\Http\Controllers\Api\Staff\WorkingHourController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::prefix('staff')->group(function () {
    Route::post('/apply', [StaffApplicationController::class, 'store']);
    Route::get('/designations', [DesignationController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('admin')->middleware('admin')->group(function () {

        Route::prefix('services')->group(function () {
            Route::get('/', [ServicesController::class, 'index']);
            Route::post('/', [ServicesController::class, 'store']);
            Route::get('{service}', [ServicesController::class, 'show']);
            Route::put('{service}', [ServicesController::class, 'update']);
            Route::delete('{service}', [ServicesController::class, 'destroy']);
        });

        Route::prefix('appointments')->group(function () {
            Route::get('/', [AdminAppointmentController::class, 'index']);
            Route::post('/', [AdminAppointmentController::class, 'store']);
            Route::get('{appointment}', [AdminAppointmentController::class, 'show']);
            Route::patch('{appointment}', [AdminAppointmentController::class, 'update']);
            Route::delete('{appointment}', [AdminAppointmentController::class, 'destroy']);
        });

        Route::prefix('staff')->group(function () {

            Route::prefix('requests')->group(function () {
                Route::get('/', [AdminStaffApplicationController::class, 'index']);
                Route::get('{staffApplication}', [AdminStaffApplicationController::class, 'show']);
                Route::patch('{staffApplication}/approve', [AdminStaffApplicationController::class, 'approve']);
                Route::patch('{staffApplication}/reject', [AdminStaffApplicationController::class, 'reject']);
            });
            // Staff CRUD
            Route::get('/', [StaffController::class, 'index']);
            Route::post('/', [StaffController::class, 'store']);
            Route::get('/search', [StaffController::class, 'search']);
            Route::patch('{staff}/status', [StaffController::class, 'updateEmploymentStatus']);
            Route::get('{staff}', [StaffController::class, 'show']);
            Route::put('{staff}', [StaffController::class, 'update']);
            Route::delete('{staff}', [StaffController::class, 'destroy']);
        });

        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
        });
    });

    Route::middleware(['staff'])->prefix('staff')->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index']);
        Route::get('/profile', [StaffProfileController::class, 'show']);
        Route::post('/profile', [StaffProfileController::class, 'store']);
        Route::put('/profile', [StaffProfileController::class, 'update']);
        Route::put('/change-password', [StaffProfileController::class, 'changePassword']);

        Route::prefix('services')->group(function () {
            Route::get('/', [StaffServiceController::class, 'index']);
            Route::put('/', [StaffServiceController::class, 'update']);
        });

        Route::prefix('working-hours')->group(function () {
            Route::get('/', [WorkingHourController::class, 'index']);
            Route::put('/', [WorkingHourController::class, 'update']);
        });

        Route::prefix('appointments')->group(function () {
            Route::get('/', [StaffAppointmentController::class, 'index']);
            Route::patch('{appointment}/confirm', [StaffAppointmentController::class, 'confirm']);
            Route::patch('{appointment}/complete', [StaffAppointmentController::class, 'complete']);
            Route::patch('{appointment}/cancel', [StaffAppointmentController::class, 'cancel']);
        });
    });

    Route::prefix('customer')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index']);
        Route::get('/services/{service}/staff', [CustomerServiceController::class, 'staff']);
        Route::get('/staff/{staff}/available-slots', [AvailabilityController::class, 'availableSlots']);

        Route::prefix('appointments')->group(function () {
            Route::get('/', [CustomerAppointmentController::class, 'index']);
            Route::get('{appointment}', [CustomerAppointmentController::class, 'show']);
            Route::post('/', [CustomerAppointmentController::class, 'store']);
            Route::patch('{appointment}/cancel', [CustomerAppointmentController::class, 'cancel']);
        });
    });
});
