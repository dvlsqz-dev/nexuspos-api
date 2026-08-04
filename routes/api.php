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
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\UnitController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ProductBatchController;
use App\Http\Controllers\Tenant\InventoryMovementController;
use App\Http\Controllers\Tenant\StockAlertController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DiscountController;
use App\Http\Controllers\Tenant\CashRegisterController;
use App\Http\Controllers\Tenant\CashSessionController;
use App\Http\Controllers\Tenant\CashMovementController;



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

        Route::apiResource('categories', CategoryController::class)
            ->middleware('permission:categories.manage');

        Route::apiResource('units', UnitController::class)
            ->middleware('permission:units.manage');

        Route::get('products', [ProductController::class, 'index'])
            ->middleware('permission:products.view');
        Route::post('products', [ProductController::class, 'store'])
            ->middleware('permission:products.create');
        Route::get('products/{product}', [ProductController::class, 'show'])
            ->middleware('permission:products.view');
        Route::put('products/{product}', [ProductController::class, 'update'])
            ->middleware('permission:products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->middleware('permission:products.delete');
        Route::post('products/{product}/images', [ProductController::class, 'uploadImage'])
            ->middleware('permission:products.update');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
            ->middleware('permission:products.update');
        Route::middleware('permission:batches.manage')->group(function () {
            Route::get('products/{product}/batches', [ProductBatchController::class, 'index']);
            Route::post('products/{product}/batches', [ProductBatchController::class, 'store']);
            Route::put('products/{product}/batches/{batch}', [ProductBatchController::class, 'update']);
            Route::delete('products/{product}/batches/{batch}', [ProductBatchController::class, 'destroy']);

            
        });

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('products/{product}/movements', [InventoryMovementController::class, 'index']);
            Route::get('stock-alerts', [StockAlertController::class, 'index']);
        });

        Route::post('products/{product}/movements', [InventoryMovementController::class, 'store'])
            ->middleware('permission:inventory.adjust');

        Route::middleware('permission:customers.view')->group(function () {
            Route::get('customers', [CustomerController::class, 'index']);
            Route::get('customers/nit/{nit}', [CustomerController::class, 'findByNit']);
            Route::get('customers/{customer}', [CustomerController::class, 'show']);
        });

        Route::middleware('permission:customers.manage')->group(function () {
            Route::post('customers', [CustomerController::class, 'store']);
            Route::put('customers/{customer}', [CustomerController::class, 'update']);
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
        });

        Route::apiResource('discounts', DiscountController::class)
            ->middleware('permission:discounts.manage');

        Route::apiResource('cash-registers', CashRegisterController::class)
            ->middleware('permission:branches.manage');

        Route::middleware('permission:cash.open')->post('cash-sessions/open', [CashSessionController::class, 'open']);
        Route::middleware('permission:cash.close')->post('cash-sessions/{cashSession}/close', [CashSessionController::class, 'close']);

        Route::get('cash-sessions/current', [CashSessionController::class, 'current']);

        Route::middleware('permission:cash.movements')->group(function () {
            Route::get('cash-movements', [CashMovementController::class, 'index']);
            Route::post('cash-movements', [CashMovementController::class, 'store']);
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