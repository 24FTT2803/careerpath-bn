<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\BiicfController;
use App\Http\Controllers\Admin\BusinessPlanController;
use App\Http\Controllers\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupMembershipController;
use App\Http\Controllers\Admin\OrganisationController;
use App\Http\Controllers\Admin\OrganisationGroupController;
use App\Http\Controllers\Admin\SponsorshipController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserPlanGrantController;
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboardController;
use App\Http\Controllers\Student\BiicfExplorerController;
use App\Http\Controllers\Student\CareerAdviserController;
use App\Http\Controllers\Student\CareerRecommendationController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\HistoryController;
use App\Http\Controllers\Student\MilestoneController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\LecturerMiddleware;
use App\Http\Middleware\RoleMiddleware;
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
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->role === 'lecturer') {
        return redirect()->route('lecturer.dashboard');
    }

    return redirect()->route('student.dashboard');
})
    ->middleware('auth')
    ->name('dashboard');

// ============================================
// AUTHENTICATION ROUTES (Provided by Breeze)
// ============================================
require __DIR__.'/auth.php';

// ============================================
// STUDENT ROUTES
// ============================================
Route::middleware([
    'auth',
    RoleMiddleware::class.':student',
])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Career Recommendations
        Route::get(
            '/career-analysis/{recommendation}',
            [CareerRecommendationController::class, 'analysis']
        )->name('recommendations.analysis');

        Route::get(
            '/recommendations',
            [CareerRecommendationController::class, 'index']
        )->name('recommendations.index');

        Route::post(
            '/recommendations/generate',
            [CareerRecommendationController::class, 'generate']
        )->name('recommendations.generate');

        /*
         * The career report for one historical generation.
         * Ownership is checked in the controller.
         */
        Route::get(
            '/recommendations/{generation}/report',
            [CareerRecommendationController::class, 'report']
        )->name('recommendations.report');

        // History / Archive
        Route::get(
            '/history',
            [HistoryController::class, 'index']
        )->name('history');

        // Career Adviser
        Route::get(
            '/career-adviser',
            [CareerAdviserController::class, 'index']
        )->name('career-adviser');

        Route::post(
            '/career-adviser/ask',
            [CareerAdviserController::class, 'ask']
        )->name('career-adviser.ask');

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

        Route::post(
            '/settings/upgrade',
            [ProfileController::class, 'upgrade']
        )->name('settings.upgrade');

        Route::put(
            '/settings/preferences',
            [ProfileController::class, 'updatePreferences']
        )->name('settings.preferences');

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

        Route::get(
        '/notifications/recent',
        [ProfileController::class, 'recentNotifications']
        )->name('notifications.recent');

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
                    '/job-roles-search',
                    [BiicfExplorerController::class, 'searchJobRoles']
                )->name('job-roles.search');

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
// STAFF ACCESS TO STUDENT PROFILE EXPORT
// ============================================
Route::middleware([
    'auth',
    LecturerMiddleware::class,
])
    ->get(
        '/student/profile/export/{userId}/{generation?}',
        [ProfileController::class, 'exportAdmin']
    )
    ->name('student.profile.export.admin');

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
                LecturerMiddleware::class
            );

        Route::get(
            '/students',
            [AdminStudentController::class, 'index']
        )
            ->name('students.index')
            ->middleware(
                LecturerMiddleware::class
            );

        Route::get(
            '/students/{id}',
            [AdminStudentController::class, 'show']
        )
            ->name('students.show')
            ->middleware(
                LecturerMiddleware::class
            );

        // CAREER ROUTES
        Route::get(
            '/careers',
            [AdminCareerController::class, 'index']
        )
            ->name('careers.index')
            ->middleware(
                LecturerMiddleware::class
            );

        Route::get(
            '/careers/{id}',
            [AdminCareerController::class, 'show']
        )
            ->name('careers.show')
            ->middleware(
                LecturerMiddleware::class
            );

        // View milestone proof for admin/lecturer
        Route::get('/students/{studentId}/milestones/{milestoneId}/proof', [MilestoneController::class, 'viewProofAdmin'])
            ->name('milestones.proof')
            ->middleware(LecturerMiddleware::class);

        // ============================================
        // BUSINESS MANAGEMENT (Admin ONLY)
        // ============================================
        Route::middleware(
            AdminMiddleware::class
        )
            ->prefix('business')
            ->name('business.')
            ->group(function () {

                Route::get(
                    '/plans',
                    [
                        BusinessPlanController::class,
                        'index',
                    ]
                )->name('plans.index');

                Route::put(
                    '/plans/{plan}',
                    [
                        BusinessPlanController::class,
                        'updatePlan',
                    ]
                )->name('plans.update');

                Route::put(
                    '/features/{feature}/global',
                    [
                        BusinessPlanController::class,
                        'updateGlobalFeature',
                    ]
                )->name(
                    'features.global'
                );

                /*
                 * The institutions whose structures this
                 * platform holds.
                 */
                Route::post(
                    '/organisations',
                    [OrganisationController::class, 'store']
                )->name('organisations.store');

                Route::put(
                    '/organisations/{organisation}',
                    [OrganisationController::class, 'update']
                )->name('organisations.update');

                Route::put(
                    '/organisations/{organisation}/archive',
                    [OrganisationController::class, 'archive']
                )->name('organisations.archive');

                Route::put(
                    '/organisations/{organisation}/restore',
                    [OrganisationController::class, 'restore']
                )->name('organisations.restore');

                Route::delete(
                    '/organisations/{organisation}',
                    [OrganisationController::class, 'destroy']
                )->name('organisations.destroy');

                /*
                 * Organisations funding access for groups of
                 * students.
                 */
                Route::get(
                    '/sponsorship',
                    [SponsorshipController::class, 'index']
                )->name('sponsorship.index');

                Route::post(
                    '/sponsorship/sponsors',
                    [SponsorshipController::class, 'storeSponsor']
                )->name('sponsorship.sponsors.store');

                Route::put(
                    '/sponsorship/sponsors/{sponsor}',
                    [SponsorshipController::class, 'updateSponsor']
                )->name('sponsorship.sponsors.update');

                Route::put(
                    '/sponsorship/sponsors/{sponsor}/toggle',
                    [SponsorshipController::class, 'toggleSponsor']
                )->name('sponsorship.sponsors.toggle');

                Route::delete(
                    '/sponsorship/sponsors/{sponsor}',
                    [SponsorshipController::class, 'destroySponsor']
                )->name('sponsorship.sponsors.destroy');

                Route::post(
                    '/sponsorship/grants',
                    [SponsorshipController::class, 'storeGrant']
                )->name('sponsorship.grants.store');

                Route::put(
                    '/sponsorship/grants/{grant}/revoke',
                    [SponsorshipController::class, 'revokeGrant']
                )->name('sponsorship.grants.revoke');

                /*
                 * Institutions, schools, programmes, intakes,
                 * sessions and classes.
                 */
                Route::get(
                    '/groups',
                    [OrganisationGroupController::class, 'index']
                )->name('groups.index');

                Route::get(
                    '/groups/create',
                    [OrganisationGroupController::class, 'create']
                )->name('groups.create');

                Route::post(
                    '/groups',
                    [OrganisationGroupController::class, 'store']
                )->name('groups.store');

                Route::get(
                    '/groups/{group}/edit',
                    [OrganisationGroupController::class, 'edit']
                )->name('groups.edit');

                Route::put(
                    '/groups/{group}',
                    [OrganisationGroupController::class, 'update']
                )->name('groups.update');

                Route::put(
                    '/groups/{group}/move',
                    [OrganisationGroupController::class, 'move']
                )->name('groups.move');

                Route::put(
                    '/groups/{group}/archive',
                    [OrganisationGroupController::class, 'archive']
                )->name('groups.archive');

                Route::put(
                    '/groups/{group}/restore',
                    [OrganisationGroupController::class, 'restore']
                )->name('groups.restore');

                Route::delete(
                    '/groups/{group}',
                    [OrganisationGroupController::class, 'destroy']
                )->name('groups.destroy');

                /*
                 * Who belongs to a group. Class membership is
                 * assigned here; programme membership follows
                 * the student's profile.
                 */
                Route::get(
                    '/groups/{group}/members',
                    [GroupMembershipController::class, 'index']
                )->name('groups.members.index');

                Route::post(
                    '/groups/{group}/members',
                    [GroupMembershipController::class, 'store']
                )->name('groups.members.store');

                Route::delete(
                    '/groups/{group}/members/{user}',
                    [GroupMembershipController::class, 'destroy']
                )->name('groups.members.destroy');

                /*
                 * The levels an institution uses are its own to
                 * name, so types are managed here rather than
                 * fixed in code.
                 */
                Route::post(
                    '/group-types',
                    [OrganisationGroupController::class, 'storeType']
                )->name('groups.types.store');

                Route::delete(
                    '/group-types/{type}',
                    [OrganisationGroupController::class, 'destroyType']
                )->name('groups.types.destroy');

                /*
                 * Access granted to individual accounts, for
                 * special and test users.
                 */
                Route::get(
                    '/grants',
                    [
                        UserPlanGrantController::class,
                        'index',
                    ]
                )->name('grants.index');

                Route::post(
                    '/grants',
                    [
                        UserPlanGrantController::class,
                        'store',
                    ]
                )->name('grants.store');

                Route::put(
                    '/grants/{grant}/revoke',
                    [
                        UserPlanGrantController::class,
                        'revoke',
                    ]
                )->name('grants.revoke');

                /*
                 * What appears in the ad placements. Whether the
                 * placements render at all is decided by the
                 * plan features above.
                 */
                Route::put(
                    '/advertisements/{advertisement}/reorder',
                    [AdvertisementController::class, 'reorder']
                )->name('advertisements.reorder');

                Route::put(
                    '/advertisement-slots/{slot}',
                    [AdvertisementController::class, 'updateSlot']
                )->name('advertisements.slots.update');

                Route::resource(
                    'advertisements',
                    AdvertisementController::class
                )->except(['show']);
            });

        // ============================================
        // BIICF MANAGEMENT (Admin ONLY)
        // ============================================
        Route::middleware(AdminMiddleware::class)->group(function () {

            Route::get('/biicf', function () {
                return redirect()->route('admin.biicf.sub-sectors');
            })->name('biicf');

            // Sub-sectors - Use 'biicf.' NOT 'admin.biicf.'
            Route::get('/biicf/sub-sectors', [BiicfController::class, 'subSectors'])->name('biicf.sub-sectors');
            Route::get('/biicf/sub-sectors/create', [BiicfController::class, 'subSectorCreate'])->name('biicf.sub-sectors.create');
            Route::post('/biicf/sub-sectors', [BiicfController::class, 'subSectorStore'])->name('biicf.sub-sectors.store');
            Route::get('/biicf/sub-sectors/{subSector}/edit', [BiicfController::class, 'subSectorEdit'])->name('biicf.sub-sectors.edit');
            Route::put('/biicf/sub-sectors/{subSector}', [BiicfController::class, 'subSectorUpdate'])->name('biicf.sub-sectors.update');
            Route::delete('/biicf/sub-sectors/{subSector}', [BiicfController::class, 'subSectorDestroy'])->name('biicf.sub-sectors.destroy');

            // Job Roles - Use 'biicf.' NOT 'admin.biicf.'
            Route::get('/biicf/job-roles', [BiicfController::class, 'jobRoles'])->name('biicf.job-roles');
            Route::get('/biicf/job-roles/create', [BiicfController::class, 'jobRoleCreate'])->name('biicf.job-roles.create');
            Route::post('/biicf/job-roles', [BiicfController::class, 'jobRoleStore'])->name('biicf.job-roles.store');
            Route::get('/biicf/job-roles/{jobRole}', [BiicfController::class, 'jobRoleShow'])->name('biicf.job-roles.show');
            Route::get('/biicf/job-roles/{jobRole}/edit', [BiicfController::class, 'jobRoleEdit'])->name('biicf.job-roles.edit');
            Route::put('/biicf/job-roles/{jobRole}', [BiicfController::class, 'jobRoleUpdate'])->name('biicf.job-roles.update');
            Route::delete('/biicf/job-roles/{jobRole}', [BiicfController::class, 'jobRoleDestroy'])->name('biicf.job-roles.destroy');

            // Competencies - Use 'biicf.' NOT 'admin.biicf.'
            Route::get('/biicf/competencies', [BiicfController::class, 'competencies'])->name('biicf.competencies');
            Route::get('/biicf/competencies/create', [BiicfController::class, 'competencyCreate'])->name('biicf.competencies.create');
            Route::post('/biicf/competencies', [BiicfController::class, 'competencyStore'])->name('biicf.competencies.store');
            Route::get('/biicf/competencies/{competency}/edit', [BiicfController::class, 'competencyEdit'])->name('biicf.competencies.edit');
            Route::put('/biicf/competencies/{competency}', [BiicfController::class, 'competencyUpdate'])->name('biicf.competencies.update');
            Route::delete('/biicf/competencies/{competency}', [BiicfController::class, 'competencyDestroy'])->name('biicf.competencies.destroy');

            // Proficiency Levels - Use 'biicf.' NOT 'admin.biicf.'
            Route::get('/biicf/proficiency-levels', [BiicfController::class, 'proficiencyLevels'])->name('biicf.proficiency-levels');
            Route::get('/biicf/proficiency-levels/create', [BiicfController::class, 'proficiencyLevelCreate'])->name('biicf.proficiency-levels.create');
            Route::post('/biicf/proficiency-levels', [BiicfController::class, 'proficiencyLevelStore'])->name('biicf.proficiency-levels.store');
            Route::get('/biicf/proficiency-levels/{level}/edit', [BiicfController::class, 'proficiencyLevelEdit'])->name('biicf.proficiency-levels.edit');
            Route::put('/biicf/proficiency-levels/{level}', [BiicfController::class, 'proficiencyLevelUpdate'])->name('biicf.proficiency-levels.update');
            Route::delete('/biicf/proficiency-levels/{level}', [BiicfController::class, 'proficiencyLevelDestroy'])->name('biicf.proficiency-levels.destroy');

            // Trainings - Use 'biicf.' NOT 'admin.biicf.'
            Route::get('/biicf/trainings', [BiicfController::class, 'trainings'])->name('biicf.trainings');
            Route::get('/biicf/trainings/create', [BiicfController::class, 'trainingCreate'])->name('biicf.trainings.create');
            Route::post('/biicf/trainings', [BiicfController::class, 'trainingStore'])->name('biicf.trainings.store');
            Route::get('/biicf/trainings/{training}/edit', [BiicfController::class, 'trainingEdit'])->name('biicf.trainings.edit');
            Route::put('/biicf/trainings/{training}', [BiicfController::class, 'trainingUpdate'])->name('biicf.trainings.update');
            Route::delete('/biicf/trainings/{training}', [BiicfController::class, 'trainingDestroy'])->name('biicf.trainings.destroy');
        });

        // Admin ONLY routes - Users
        Route::middleware(AdminMiddleware::class)->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
    });

// ============================================
// LECTURER ROUTES
// ============================================
Route::middleware(['auth', LecturerMiddleware::class])
    ->prefix('lecturer')
    ->name('lecturer.')
    ->group(function () {
        Route::get(
            '/dashboard',
            [LecturerDashboardController::class, 'index']
        )->name('dashboard');
    });
