<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\TenantApprovalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Rutas públicas (sin autenticación) ---
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('login', [LoginController::class, 'store']);
    Route::post('admin/login', [AdminLoginController::class, 'store']);

    // --- Rutas de negocio (usuarios de tenant) ---
    Route::middleware(['auth:sanctum', 'tenant.user', 'resolve.tenant'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy']);

        // Aquí se irán agregando las rutas de cada módulo:
        // products, sales, customers, etc. según avancemos features.
    });

    // --- Rutas de la plataforma (platform admin) ---
    Route::prefix('admin')->middleware(['auth:sanctum', 'platform.admin'])->group(function () {
        Route::post('logout', [AdminLoginController::class, 'destroy']);

        Route::get('tenants/pending', [TenantApprovalController::class, 'pending']);
        Route::get('tenants/{tenant}/documents/{document}', [TenantApprovalController::class, 'showDocument']);
        Route::post('tenants/{tenant}/approve', [TenantApprovalController::class, 'approve']);
        Route::post('tenants/{tenant}/reject', [TenantApprovalController::class, 'reject']);
    });
});