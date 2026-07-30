<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\TenantApprovalController;
use App\Http\Controllers\Tenant\BranchController;
use App\Http\Controllers\Tenant\ConfigController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\RoleController;


Route::prefix('v1')->group(function () {

    // --- Rutas públicas (sin autenticación) ---
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('login', [LoginController::class, 'store']);
    Route::post('admin/login', [AdminLoginController::class, 'store']);

    // --- Rutas de negocio (usuarios de tenant) ---
    Route::middleware(['auth:sanctum', 'tenant.user', 'resolve.tenant'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy']);

        Route::apiResource('branches', BranchController::class)->middleware('permission:branches.manage');

        Route::get('tenant/config', [ConfigController::class, 'show']);
        Route::put('tenant/config', [ConfigController::class, 'update'])->middleware('permission:tenant.config');

        Route::get('tenant/roles', [RoleController::class, 'index']);

        Route::middleware('permission:users.manage')->group(function () {
            Route::get('tenant/users', [UserController::class, 'index']);
            Route::post('tenant/users', [UserController::class, 'store']);
            Route::put('tenant/users/{user}', [UserController::class, 'update']);
            Route::delete('tenant/users/{user}', [UserController::class, 'destroy']);
        });
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