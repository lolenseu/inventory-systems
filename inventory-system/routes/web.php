<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopAIController;

// Redirect root to the login page
Route::redirect('/', '/login');

// Authentication routes (login/register/logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
     
    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
});
 
// Main ShopAI page
Route::get('/', [ShopAIController::class, 'index'])->name('shopai.index');
Route::get('/shopai', [ShopAIController::class, 'index'])->name('shopai.home');

// Authentication routes
Route::prefix('api')->group(function () {
    Route::post('/register', [ShopAIController::class, 'register'])->name('customer.register');
    Route::post('/login', [ShopAIController::class, 'login'])->name('customer.login');
    Route::post('/logout', [ShopAIController::class, 'logout'])->name('customer.logout')->middleware('auth');
    
    // Protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ShopAIController::class, 'profile'])->name('customer.profile');
        Route::put('/profile', [ShopAIController::class, 'updateProfile'])->name('customer.updateProfile');
        Route::put('/change-password', [ShopAIController::class, 'changePassword'])->name('customer.changePassword');
    });
});