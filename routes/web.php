<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TravelOrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard.index'));
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account/email', [AccountController::class, 'updateEmail'])->name('account.email.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    // Account management (OIC / Admin; enforced by UserPolicy)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');

    Route::prefix('travel-orders')->name('travel-orders.')->group(function () {
        Route::post('/', [TravelOrderController::class, 'store'])->name('store');
        Route::get('/{travelOrder}/edit', [TravelOrderController::class, 'edit'])->name('edit');
        Route::put('/{travelOrder}', [TravelOrderController::class, 'update'])->name('update');
        Route::patch('/{travelOrder}/status', [TravelOrderController::class, 'updateStatus'])->name('status');
        Route::post('/{travelOrder}/upload', [TravelOrderController::class, 'upload'])->name('upload');
    });
});
