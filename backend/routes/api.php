<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 1. Authentication & Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::put('/auth/profile', [AuthController::class, 'updateProfile'])->name('api.auth.profile');
    });

    /*
    |--------------------------------------------------------------------------
    | 2. Public Catalog Routes (Categories & Products)
    |--------------------------------------------------------------------------
    */
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('api.categories.show');

    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

    /*
    |--------------------------------------------------------------------------
    | 3. Authenticated Customer & Admin Order Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('api.orders.show');
        Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('api.orders.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | 4. Administrator Protected Management Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        // User Management (CRUD)
        Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('api.users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');

        // Category Management (Admin CRUD)
        Route::post('/categories', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

        // Product Management (Admin CRUD & Inventory Control)
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
        Route::patch('/products/{id}/stock', [ProductController::class, 'updateStock'])->name('api.products.stock');

        // Order Administration
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('api.orders.status');

        // Analytics & System Audit Trail
        Route::get('/admin/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('api.admin.stats');
        Route::get('/admin/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('api.admin.audit');
    });

});

