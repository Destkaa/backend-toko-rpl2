<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\PublicController; // <-- Added
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. PUBLIC ROUTES (Tanpa Token / Login)
// ==========================================

// Autentikasi (Register & Login)
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Katalog Publik (Akses Tanpa Login)
Route::prefix('public')->group(function () {
    Route::get('/produk', [PublicController::class, 'produk']);
    Route::get('/produk/{id}', [PublicController::class, 'detailProduk']);
    Route::get('/kategori', [PublicController::class, 'kategori']);
    Route::get('/kategori/{id}/produk', [PublicController::class, 'produkByKategori']);
    Route::get('/search', [PublicController::class, 'search']);
});


// ==========================================
// 2. PROTECTED ROUTES (Harus Login / Token Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Profile & Logout
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'profile');
        Route::post('/logout', 'logout');
    });

    // Master Data & Pesanan Resources (CRUD Otomatis)
    Route::apiResource('kategori', KategoriController::class);
    Route::apiResource('pelanggan', PelangganController::class);
    Route::apiResource('produk', ProdukController::class);
    Route::apiResource('pesanan', PesananController::class);
});