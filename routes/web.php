<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/menu/{menu}', [MarketplaceController::class, 'show'])->name('menus.show');
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::put('/profil', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/menu', [MenuController::class, 'store'])->middleware('role:merchant')->name('menus.store');
    Route::put('/menu/{menu}', [MenuController::class, 'update'])->middleware('role:merchant')->name('menus.update');
    Route::delete('/menu/{menu}', [MenuController::class, 'destroy'])->middleware('role:merchant')->name('menus.destroy');
    Route::post('/pesanan', [OrderController::class, 'store'])->middleware('role:customer')->name('orders.store');
    Route::post('/pesanan/{order}/rating', [OrderController::class, 'rateMenu'])->middleware('role:customer')->name('orders.rate');
    Route::get('/invoice/{order}', [OrderController::class, 'invoice'])->name('invoices.show');
    Route::patch('/pesanan/{order}/status', [OrderController::class, 'updateStatus'])->middleware('role:merchant')->name('orders.status');
});
