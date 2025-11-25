<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopAIController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReportsController;

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
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
});
 
// Main ShopAI page
Route::get('/shopai', [ShopAIController::class, 'index'])->name('shopai.home');

// Search route
Route::get('/shopai/search', [ShopAIController::class, 'search'])->name('shopai.search');

// API routes
Route::prefix('shopai')->group(function () {
    Route::post('/register', [ShopAIController::class, 'register'])->name('customer.register');
    Route::post('/login', [ShopAIController::class, 'login'])->name('customer.login');
    Route::post('/logout', [ShopAIController::class, 'logout'])->name('customer.logout');
    
    // Product routes
    Route::get('/products', [ShopAIController::class, 'getProducts'])->name('customer.products');
    Route::get('/product/{id}', [ShopAIController::class, 'getProduct'])->name('customer.product');
    
    // Protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ShopAIController::class, 'profile'])->name('customer.profile');
        Route::put('/profile', [ShopAIController::class, 'updateProfile'])->name('customer.updateProfile');
        Route::put('/change-password', [ShopAIController::class, 'changePassword'])->name('customer.changePassword');
        
        // Cart routes
        Route::post('/cart/add', [ShopAIController::class, 'addToCart'])->name('customer.addToCart');
        Route::get('/cart', [ShopAIController::class, 'getCart'])->name('customer.getCart');
        Route::put('/cart/{id}', [ShopAIController::class, 'updateCart'])->name('customer.updateCart');
        Route::delete('/cart/{id}', [ShopAIController::class, 'removeCartItem'])->name('customer.removeCartItem');
        
        // Checkout route
        Route::post('/checkout', [ShopAIController::class, 'checkout'])->name('customer.checkout');
        
        // Orders routes
        Route::get('/orders', [ShopAIController::class, 'getOrders'])->name('customer.orders');
        Route::get('/order/{id}', [ShopAIController::class, 'getOrder'])->name('customer.order');
    });
});

// Legacy routes for backward compatibility
Route::middleware(['auth'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('items', ItemController::class);
});
