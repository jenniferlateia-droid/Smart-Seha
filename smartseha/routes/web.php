<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodAnalysisController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard.page');
    }

    return view('landing');
})->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.web');
    Route::post('/api/login', [AuthController::class, 'loginApi'])->name('login.api');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'showForm'])->name('profile.form');
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');

    Route::get('/profile/health', [ProfileController::class, 'showHealthForm'])->name('profile.health.form');
    Route::post('/profile/health', [ProfileController::class, 'storeHealth'])->name('profile.health.store');

    Route::get('/dashboard', [DashboardController::class, 'page'])->name('dashboard.page');
    Route::get('/food-analysis', [FoodAnalysisController::class, 'page'])->name('food.page');
    Route::get('/recommendations', [RecommendationController::class, 'page'])->name('recommendations.page');
    Route::get('/points', [PointsController::class, 'page'])->name('points.page');
    Route::get('/settings', [SettingsController::class, 'page'])->name('settings.page');
    Route::get('/admin', [AdminController::class, 'dashboard'])->middleware('admin')->name('admin.dashboard');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->middleware('admin')->name('admin.settings.update');
    Route::post('/admin/users/{user}/role', [AdminController::class, 'updateUserRole'])->middleware('admin')->name('admin.users.role');

    Route::prefix('api')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardController::class, 'data']);
        Route::get('/dashboard/tasks', [DashboardController::class, 'tasks']);
        Route::post('/dashboard/tasks/{task}/complete', [DashboardController::class, 'completeTask']);
        Route::post('/dashboard/quick-log', [DashboardController::class, 'quickLog']);

        Route::post('/analyze-food', [FoodAnalysisController::class, 'analyze']);
        Route::get('/analyze-food/{foodAnalysis}', [FoodAnalysisController::class, 'show'])->name('food.analysis.show');
        Route::get('/analyze-food-history', [FoodAnalysisController::class, 'history']);
        Route::post('/recommendations', [RecommendationController::class, 'generate']);

        Route::get('/points', [PointsController::class, 'data']);

        Route::get('/settings', [SettingsController::class, 'show']);
        Route::put('/settings', [SettingsController::class, 'update']);
    });
});
