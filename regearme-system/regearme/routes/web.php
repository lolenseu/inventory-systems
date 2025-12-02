<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Authentication routes
Route::get('/', function () {
    return view('regearme');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes with role-based access
Route::middleware(['auth'])->group(function () {

    // Main dashboard - directly check role and show appropriate dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        if ($user->role === 'officer') {
            return view('officer.dashboard', [
                'totalEquipment' => 0,
                'availableCount' => 0,
                'requestedCount' => 0,
                'approvedCount' => 0,
                'equipment' => collect()
            ]);
        } else {
            return view('user.dashboard', [
                'totalRequests' => 0,
                'availableCount' => 0,
                'requestedCount' => 0,
                'approvedCount' => 0,
                'equipment' => collect()
            ]);
        }
    })->name('dashboard');

    // Officer routes
    Route::get('/officer/dashboard', [DashboardController::class, 'officerDashboard'])->name('officer.dashboard');

    // User routes
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});