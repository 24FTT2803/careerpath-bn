<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\MilestoneController;
use App\Http\Controllers\Student\CareerRecommendationController;
use App\Http\Controllers\Student\BiicfExplorerController;
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
// PRIVACY POLICY & TERMS OF SERVICE
// ============================================
Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

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
// STUDENT ROUTES
// ============================================
Route::middleware(['auth'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Career Recommendations
        Route::get(
            '/career-analysis/{recommendation}',
            [CareerRecommendationController::class, 'analysis']
        )->name('recommendations.analysis');

        Route::post(
            '/recommendations/generate',
            [CareerRecommendationController::class, 'generate']
        )->name('recommendations.generate');

        // Dashboard
        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        // Profile
        Route::get(
            '/profile',
            [ProfileController::class, 'index']
        )->name('profile');

        Route::get(
            '/profile/edit',
            [ProfileController::class, 'edit']
        )->name('profile.edit');

        Route::put(
            '/profile',
            [ProfileController::class, 'update']
        )->name('profile.update');

        Route::delete(
            '/profile',
            [ProfileController::class, 'destroy']
        )->name('profile.destroy');

        // Profile Export
        Route::get(
            '/profile/export',
            [ProfileController::class, 'export']
        )->name('profile.export');

        Route::get(
            '/profile/export/{userId}',
            [ProfileController::class, 'exportAdmin']
        )->name('profile.export.admin');

        // Certification Evidence
        Route::get(
            '/certifications/{certification}/evidence',
            [ProfileController::class, 'certificationEvidence']
        )->name('certifications.evidence');

        // Milestones
        Route::get(
            '/milestones',
            [MilestoneController::class, 'index']
        )->name('milestones');

        Route::post(
            '/milestones',
            [MilestoneController::class, 'store']
        )->name('milestones.store');

        Route::put(
            '/milestones/{milestone}',
            [MilestoneController::class, 'complete']
        )->name('milestones.complete');

        Route::delete(
            '/milestones/{milestone}',
            [MilestoneController::class, 'destroy']
        )->name('milestones.destroy');

        // View milestone proof
        Route::get(
            '/milestones/{milestone}/proof',
            [MilestoneController::class, 'viewProof']
        )->name('milestones.proof');

        // Settings
        Route::get(
            '/settings',
            [ProfileController::class, 'settings']
        )->name('settings');

        Route::put(
            '/settings/password',
            [ProfileController::class, 'updatePassword']
        )->name('settings.password');

        // Notifications
        Route::get(
            '/notifications',
            [ProfileController::class, 'notifications']
        )->name('notifications');

        Route::post(
            '/notifications/{id}/read',
            [ProfileController::class, 'markAsRead']
        )->name('notifications.read');

        Route::post(
            '/notifications/read-all',
            [ProfileController::class, 'markAllAsRead']
        )->name('notifications.read-all');

        // BIICF Explorer
        Route::prefix('biicf-explorer')
            ->name('biicf-explorer.')
            ->group(function () {

                Route::get(
                    '/',
                    [BiicfExplorerController::class, 'index']
                )->name('index');

                Route::get(
                    '/sub-sectors/{subSector:slug}/roles',
                    [BiicfExplorerController::class, 'subSectorRoles']
                )->name('sub-sector.roles');

                Route::get(
                    '/job-roles/{jobRole:slug}',
                    [BiicfExplorerController::class, 'jobRole']
                )->name('job-role.show');

                Route::get(
                    '/job-roles/{jobRole:slug}/compare',
                    [BiicfExplorerController::class, 'compareToMe']
                )->name('job-role.compare');

                Route::get(
                    '/competencies',
                    [BiicfExplorerController::class, 'competencies']
                )->name('competencies');
            });
    });

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        )
            ->name('dashboard')
            ->middleware(
                \App\Http\Middleware\LecturerMiddleware::class
            );

        Route::get(
            '/students',
            [AdminStudentController::class, 'index']
        )
            ->name('students.index')
            ->middleware(
                \App\Http\Middleware\LecturerMiddleware::class
            );

        Route::get(
            '/students/{id}',
            [AdminStudentController::class, 'show']
        )
            ->name('students.show')
            ->middleware(
                \App\Http\Middleware\LecturerMiddleware::class
            );

        Route::get(
            '/careers',
            [AdminCareerController::class, 'index']
        )
            ->name('careers.index')
            ->middleware(
                \App\Http\Middleware\LecturerMiddleware::class
            );

        // View milestone proof for admin/lecturer
        Route::get('/students/{studentId}/milestones/{milestoneId}/proof', [MilestoneController::class, 'viewProofAdmin'])
            ->name('milestones.proof')
            ->middleware(\App\Http\Middleware\LecturerMiddleware::class);

        // Admin ONLY routes
        Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
    });