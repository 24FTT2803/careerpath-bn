<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\MilestoneController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CareerController as AdminCareerController;
use Illuminate\Support\Facades\Route;

// ============================================
// HOME ROUTE
// ============================================
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// DASHBOARD SHORTCUT
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/milestones', [MilestoneController::class, 'index'])->name('milestones');
    Route::post('/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'complete'])->name('milestones.complete');
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');
    Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read', [ProfileController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ProfileController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// ============================================
// ADMIN ROUTES (Lecturer + Admin Access)
// ============================================
Route::middleware(['auth', 'lecturer'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Students (Read-Only)
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{id}', [AdminStudentController::class, 'show'])->name('students.show');

    // Careers (Read-Only)
    Route::get('/careers', [AdminCareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/{id}', [AdminCareerController::class, 'show'])->name('careers.show');

    // Users (Admin Only - Full CRUD)
    Route::middleware(['admin'])->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});