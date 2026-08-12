<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\MilestoneController;
use Illuminate\Support\Facades\Route;

// ============================================
// HOME ROUTE
// ============================================
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// DASHBOARD SHORTCUT (Fixes the login redirect)
// ============================================
Route::get('/dashboard', function () {
    return redirect()->route('student.dashboard');
})->name('dashboard');

// ============================================
// AUTHENTICATION ROUTES (Provided by Breeze)
// ============================================
require __DIR__.'/auth.php';

// ============================================
// STUDENT ROUTES (Developer 1)
// ============================================
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Milestones
    Route::get('/milestones', [MilestoneController::class, 'index'])->name('milestones');
    Route::post('/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'complete'])->name('milestones.complete');
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');

    // Settings
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');

    // Notifications
    Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read', [ProfileController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ProfileController::class, 'markAllAsRead'])->name('notifications.read-all');
});