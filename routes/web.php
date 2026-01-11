<?php

use Illuminate\Support\Facades\Route;

// New Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmissionWorkflowController;
use App\Http\Controllers\SubmissionDiscussionController;
use App\Http\Controllers\ReviewWorkflowController;
use App\Http\Controllers\SubmissionFileController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\EditorDecisionController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JournalSelectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WorkflowSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\SectionController as AdminSectionController;
use App\Http\Controllers\Admin\JournalUserManagementController;
use App\Http\Controllers\NotificationController;

// Legacy Controllers (keep for backward compatibility)
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\AuthorController as LegacyAuthorController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\DashboardController as LegacyDashboardController;
use App\Http\Controllers\Admin\SiteAdminController;

// =====================================================
// PORTAL HOME (List of all journals)
// =====================================================
Route::get('/', [PublicController::class, 'portalHome'])->name('portal.home');
Route::get('/journals', [PublicController::class, 'journalList'])->name('portal.journals');

// File downloads (public for galley files)
Route::get('/files/{file}/download', [SubmissionFileController::class, 'download'])->name('files.download');
Route::get('/files/{file}/preview', [SubmissionFileController::class, 'preview'])->name('files.preview')->middleware('auth');
Route::get('/files/{file}/serve', [SubmissionFileController::class, 'serve'])->name('files.serve'); // Signed URL access

// =====================================================
// AUTH ROUTES
// =====================================================
Route::get('/login', [AuthController::class, 'index'])->name('login');

// Registration Routes
use App\Http\Controllers\Admin\RegisterController;
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'post'])->name('success-forgot-password');
Route::get('/change-password', [ForgotPasswordController::class, 'changePassword'])->name('change-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================
// JOURNAL SELECTION (Protected)
// =====================================================
Route::middleware(['auth'])->group(function () {
    // Redirect /dashboard to journal selection or first journal
    Route::get('/dashboard', [JournalSelectController::class, 'redirectToDashboard'])->name('dashboard');
    Route::get('/select-journal', [JournalSelectController::class, 'index'])->name('journal.select');

    // --------- Profile Settings (Global, not per-journal) ---------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // --------- Notifications API (Global) ---------
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

// =====================================================
// GLOBAL ADMIN ROUTES (Super Admin only - manages all journals)
// =====================================================


Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Super Admin'])->group(function () {
    // Site Administration Dashboard
    Route::get('/', [SiteAdminController::class, 'index'])->name('site.index');

    // Dedicated Pages
    Route::get('/site-setting', [SiteAdminController::class, 'siteSettings'])->name('site.settings.form');
    Route::get('/system-informations', [SiteAdminController::class, 'systemInfo'])->name('site.system-info');

    // Actions
    Route::post('/settings', [SiteAdminController::class, 'updateSettings'])->name('site.settings.update');
    Route::post('/expire-sessions', [SiteAdminController::class, 'expireSessions'])->name('site.expire-sessions');
    Route::post('/clear-cache', [SiteAdminController::class, 'clearDataCache'])->name('site.clear-cache');
    Route::post('/clear-templates', [SiteAdminController::class, 'clearTemplateCache'])->name('site.clear-templates');
    Route::post('/clear-logs', [SiteAdminController::class, 'clearScheduledTaskLogs'])->name('site.clear-logs');

    // Journal Management (create new journals, etc.)
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
    Route::put('/journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');
});

// =====================================================
// LEGACY ADMIN ROUTES (kept for backward compatibility)
// =====================================================
Route::group(['middleware' => ['auth'], 'prefix' => 'admin-legacy'], function () {
    // Admin Management
    Route::resource('admin', AdminController::class);
    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);

    // Publisher Management
    Route::prefix('publisher')->group(function () {
        Route::post('import-data', [PublisherController::class, 'importData'])->name('publisher.import');
        Route::get('export-data', [PublisherController::class, 'exportData'])->name('publisher.export');
        Route::get('download-template', [PublisherController::class, 'downloadTemplate'])->name('publisher.template');
    });
    Route::resource('publisher', PublisherController::class)->whereUuid('publisher');

    // Author Management
    Route::prefix('author')->group(function () {
        Route::post('import-data', [LegacyAuthorController::class, 'importData'])->name('author.import');
        Route::get('export-data', [LegacyAuthorController::class, 'exportData'])->name('author.export');
        Route::get('download-template', [LegacyAuthorController::class, 'downloadTemplate'])->name('author.template');
    });
    Route::resource('author', LegacyAuthorController::class)->whereUuid('author');

    // Profile
    Route::get('edit-profile-admin', [AdminController::class, 'editProfile'])
        ->name('profile-admin.edit')->withoutMiddleware('permission:admin');
    Route::put('edit-profile-admin', [AdminController::class, 'updateProfile'])
        ->name('profile-admin.update')->withoutMiddleware('permission:admin');

    // Dashboard
    Route::get('dashboard', [LegacyDashboardController::class, 'index'])->name('dashboard.index');

    // Article & Media Corner
    Route::resource('article', ArticleController::class);
    Route::patch('article/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('article.toggle-status');

    // Application Settings
    Route::get('application-setting', [ApplicationSettingController::class, 'index'])->name('application-setting.index');
    Route::post('application-setting/backup', [ApplicationSettingController::class, 'backupDatabase'])->name('application-setting.backup');
    Route::get('application-setting/system-info', [ApplicationSettingController::class, 'getSystemInfo'])->name('application-setting.system-info');
    Route::get('application-setting/database-info', [ApplicationSettingController::class, 'getDatabaseInfo'])->name('application-setting.database-info');
    Route::post('application-setting/upload-ad-art', [ApplicationSettingController::class, 'uploadAdArt'])->name('application-setting.upload-ad-art');
    Route::get('application-setting/download-ad-art', [ApplicationSettingController::class, 'downloadAdArt'])->name('application-setting.download-ad-art');
    Route::delete('application-setting/delete-ad-art', [ApplicationSettingController::class, 'deleteAdArt'])->name('application-setting.delete-ad-art');
});

// =====================================================
// TRANSLATE (Public)
// =====================================================
Route::get('translate', [TranslateController::class, 'index'])->name('translate');
Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');

// =====================================================
// JOURNAL-SCOPED PUBLIC ROUTES (Per-Journal Frontend)
// These come AFTER all other routes to catch journal slugs
// =====================================================
Route::prefix('{journal}')->group(function () {

    // --------- Public Journal Pages ---------
    Route::get('/', [PublicController::class, 'journalHome'])->name('journal.public.home');
    Route::get('/current', [PublicController::class, 'currentIssue'])->name('journal.public.current');
    Route::get('/archives', [PublicController::class, 'archives'])->name('journal.public.archives');
    Route::get('/about', [PublicController::class, 'about'])->name('journal.public.about');
    Route::get('/author-guidelines', [PublicController::class, 'authorGuidelines'])->name('journal.public.author-guidelines');
    Route::get('/editorial-team', [PublicController::class, 'editorialTeam'])->name('journal.public.editorial-team');
    Route::get('/search', [SearchController::class, 'index'])->name('journal.public.search');
    Route::get('/search/quick', [SearchController::class, 'quickSearch'])->name('journal.public.search.quick');
    Route::get('/issue/{issue}', [PublicController::class, 'issue'])->name('journal.public.issue');
    Route::get('/article/{submission}', [PublicController::class, 'article'])->name('journal.public.article');
    Route::get('/article/{submission}/view', [PublicController::class, 'articleReader'])->name('journal.public.article.reader');

    // =====================================================
    // JOURNAL-SCOPED DASHBOARD ROUTES (Protected)
    // =====================================================
    Route::middleware(['auth', 'journal.context'])->group(function () {

        // --------- Journal Dashboard ---------
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('journal.dashboard');

        // --------- User Management (General / Manager) ---------
        Route::middleware('role:Journal Manager|Editor|Admin|Super Admin')->controller(JournalUserManagementController::class)->prefix('users')->name('journal.users.')->group(function () {
            // 1. All Users
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/{user}/login-as', 'loginAs')->name('login-as');
            Route::post('/{user}/disable', 'disable')->name('disable');
            Route::post('/{user}/enable', 'enable')->name('enable');
            Route::post('/{user}/email', 'email')->name('email');

            // 2. Roles
            Route::get('/roles', 'roles')->name('roles');
            Route::get('/roles/create', 'createRole')->name('roles.create');
            Route::post('/roles', 'storeRole')->name('roles.store');
            Route::get('/roles/{role}/edit', 'editRole')->name('roles.edit');
            Route::put('/roles/{role}', 'updateRole')->name('roles.update');
            Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
            Route::post('/roles/{role}/reset', 'resetRolePermissions')->name('roles.reset');

            // 3. Site Access Options
            Route::get('/access', 'access')->name('access');
            Route::post('/access', 'updateAccess')->name('access.update');
        });

        // --------- Submissions (Author) ---------
        Route::resource('submissions', SubmissionController::class)->names([
            'index' => 'journal.submissions.index',
            'create' => 'journal.submissions.create',
            'store' => 'journal.submissions.store',
            'show' => 'journal.submissions.show',
            'edit' => 'journal.submissions.edit',
            'update' => 'journal.submissions.update',
            'destroy' => 'journal.submissions.destroy',
        ]);

        // --------- Submission Files ---------
        Route::post('/submissions/{submission}/files', [SubmissionFileController::class, 'store'])->name('journal.submissions.files.store');
        Route::delete('/files/{file}', [SubmissionFileController::class, 'destroy'])->name('journal.files.destroy');

        // --------- TinyMCE Image Upload ---------
        Route::post('/upload/image', [SubmissionController::class, 'uploadImage'])->name('journal.upload.image');

        // --------- Discussion (Shared) ---------
        Route::post('/discussion/upload-image', [SubmissionDiscussionController::class, 'uploadCkeditorImage'])->name('journal.discussion.upload-image');
        Route::post('/discussion/upload-file', [SubmissionDiscussionController::class, 'uploadDiscussionFile'])->name('journal.discussion.upload-file');
        Route::get('/discussion/file/{file}', [SubmissionDiscussionController::class, 'download'])->name('journal.discussion.file.download');
        Route::post('/{submission}/discussion/create', [SubmissionDiscussionController::class, 'store'])->name('journal.discussion.create');
        Route::post('/{submission}/discussion/{discussion}/reply', [SubmissionDiscussionController::class, 'storeReply'])->name('journal.discussion.reply');

        // --------- Submission Workflow (OJS 3.3 Style) ---------
        Route::prefix('workflow')->name('journal.workflow.')->middleware('role:Editor|Section Editor|Admin|Super Admin')->group(function () {
            Route::post('/{submission}/file', [SubmissionWorkflowController::class, 'uploadFile'])->name('file.store');
            Route::post('/{submission}/discussion', [SubmissionWorkflowController::class, 'storeDiscussion'])->name('discussion.store');

            Route::get('/{submission}', [SubmissionWorkflowController::class, 'show'])->name('show');
            Route::post('/{submission}/assign-editor', [SubmissionWorkflowController::class, 'assignEditor'])->name('assign-editor');
            Route::delete('/{submission}/remove-editor/{assignment}', [SubmissionWorkflowController::class, 'removeEditor'])->name('remove-editor');
            Route::post('/{submission}/change-stage', [SubmissionWorkflowController::class, 'changeStage'])->name('change-stage');
            Route::post('/{submission}/schedule-publication', [SubmissionWorkflowController::class, 'schedulePublication'])->name('schedule-publication');

            // Review Workflow Routes
            Route::get('/reviewers/search', [ReviewWorkflowController::class, 'searchReviewers'])->name('reviewers.search');
            Route::post('/{submission}/assign-reviewer', [ReviewWorkflowController::class, 'assignReviewer'])->name('assign-reviewer');
            Route::delete('/{submission}/unassign-reviewer/{assignment}', [ReviewWorkflowController::class, 'unassignReviewer'])->name('unassign-reviewer');
            Route::post('/{submission}/record-decision', [ReviewWorkflowController::class, 'recordDecision'])->name('record-decision');
            Route::post('/{submission}/promote-to-copyediting', [ReviewWorkflowController::class, 'promoteToCopyediting'])->name('promote-copyediting');
            Route::post('/{submission}/send-to-production', [ReviewWorkflowController::class, 'sendToProduction'])->name('send-production');

            // Enhanced Workflow Actions (OJS 3.3 Editorial Decisions)
            Route::get('/{submission}/available-files', [SubmissionWorkflowController::class, 'getAvailableFiles'])->name('available-files');
            Route::post('/{submission}/promote-to-review', [SubmissionWorkflowController::class, 'promoteToReview'])->name('promote-review');
            Route::post('/{submission}/skip-review', [SubmissionWorkflowController::class, 'skipReview'])->name('skip-review');
            Route::post('/{submission}/decline', [SubmissionWorkflowController::class, 'decline'])->name('decline');
        });

        // --------- Editorial (Editor) ---------
        Route::prefix('editorial')->name('journal.editorial.')->middleware('role:Editor|Admin|Super Admin')->group(function () {
            Route::get('/queue', [EditorialController::class, 'queue'])->name('queue');
            Route::get('/archives', [EditorialController::class, 'archives'])->name('archives');
            Route::post('/{submission}/assign', [EditorialController::class, 'assign'])->name('assign');
            Route::post('/{submission}/accept', [EditorialController::class, 'accept'])->name('accept');
            Route::post('/{submission}/reject', [EditorialController::class, 'reject'])->name('reject');
            Route::post('/{submission}/revision', [EditorialController::class, 'requestRevision'])->name('revision');
        });

        // --------- Issues (Editor) ---------
        Route::middleware('role:Editor|Admin|Super Admin')->group(function () {
            Route::resource('issues', IssueController::class)->names([
                'index' => 'journal.issues.index',
                'create' => 'journal.issues.create',
                'store' => 'journal.issues.store',
                'show' => 'journal.issues.show',
                'edit' => 'journal.issues.edit',
                'update' => 'journal.issues.update',
                'destroy' => 'journal.issues.destroy',
            ]);
            Route::post('/issues/{issue}/publish', [IssueController::class, 'publish'])->name('journal.issues.publish');
            Route::post('/issues/{issue}/unpublish', [IssueController::class, 'unpublish'])->name('journal.issues.unpublish');
            Route::post('/issues/{issue}/add-articles', [IssueController::class, 'addArticles'])->name('journal.issues.add-articles');
            Route::delete('/issues/{issue}/remove-article/{submission}', [IssueController::class, 'removeArticle'])->name('journal.issues.remove-article');
        });

        // --------- Reviewer Workflow ---------
        Route::prefix('reviewer')->name('journal.reviewer.')->middleware('role:Reviewer|Editor|Admin|Super Admin')->group(function () {
            Route::get('/', [ReviewerController::class, 'index'])->name('index');
            Route::get('/{assignment}', [ReviewerController::class, 'show'])->name('show');
            Route::post('/{assignment}/accept', [ReviewerController::class, 'accept'])->name('accept');
            Route::post('/{assignment}/decline', [ReviewerController::class, 'decline'])->name('decline');
            Route::post('/{assignment}/submit', [ReviewerController::class, 'submit'])->name('submit');
        });

        // --------- Editor Decision Workflow ---------
        Route::prefix('editor')->name('journal.editor.')->middleware('role:Editor|Admin|Super Admin')->group(function () {
            Route::get('/submission/{submission}', [EditorDecisionController::class, 'show'])->name('show');
            Route::post('/submission/{submission}/assign-reviewer', [EditorDecisionController::class, 'assignReviewer'])->name('assign-reviewer');
            Route::delete('/reviewer/{assignment}', [EditorDecisionController::class, 'cancelReviewer'])->name('cancel-reviewer');
            Route::post('/submission/{submission}/decision', [EditorDecisionController::class, 'recordDecision'])->name('decision');
            Route::post('/submission/{submission}/send-to-review', [EditorDecisionController::class, 'sendToReview'])->name('send-to-review');
        });

        // --------- Journal Settings (Manager Level) ---------
        Route::middleware('role:Journal Manager|Editor|Admin|Super Admin')->prefix('settings')->name('journal.settings.')->group(function () {
            Route::get('/', [JournalController::class, 'settings'])->name('index');
            Route::put('/', [JournalController::class, 'updateSettings'])->name('update');

            // Sections CRUD
            Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
            Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
            Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

            // Categories CRUD
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            // Workflow Settings
            Route::prefix('workflow')->name('workflow.')->group(function () {
                Route::get('/', [WorkflowSettingsController::class, 'index'])->name('index');
                Route::put('/', [WorkflowSettingsController::class, 'updateSettings'])->name('update');

                // Submission Checklists
                Route::post('/checklists', [WorkflowSettingsController::class, 'storeChecklist'])->name('checklists.store');
                Route::put('/checklists/{checklist}', [WorkflowSettingsController::class, 'updateChecklist'])->name('checklists.update');
                Route::delete('/checklists/{checklist}', [WorkflowSettingsController::class, 'destroyChecklist'])->name('checklists.destroy');

                // Review Forms
                Route::post('/review-forms', [WorkflowSettingsController::class, 'storeReviewForm'])->name('review-forms.store');
                Route::put('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'updateReviewForm'])->name('review-forms.update');
                Route::delete('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'destroyReviewForm'])->name('review-forms.destroy');

                // Library Files
                Route::post('/library', [WorkflowSettingsController::class, 'storeLibraryFile'])->name('library.store');
                Route::get('/library/{libraryFile}/download', [WorkflowSettingsController::class, 'downloadLibraryFile'])->name('library.download');
                Route::delete('/library/{libraryFile}', [WorkflowSettingsController::class, 'destroyLibraryFile'])->name('library.destroy');

                // Email Templates
                Route::put('/email-templates/{emailTemplate}', [WorkflowSettingsController::class, 'updateEmailTemplate'])->name('email-templates.update');
                Route::post('/email-templates/{emailTemplate}/toggle', [WorkflowSettingsController::class, 'toggleEmailTemplate'])->name('email-templates.toggle');
                Route::post('/email-templates/{emailTemplate}/reset', [WorkflowSettingsController::class, 'resetEmailTemplate'])->name('email-templates.reset');
            });

            // Distribution Settings
            Route::controller(\App\Http\Controllers\Admin\DistributionSettingsController::class)->prefix('distribution')->name('distribution.')->group(function () {
                Route::get('/', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
            });
        });

        // --------- Journal Admin Routes ---------
        Route::prefix('admin')->name('journal.admin.')->middleware('role:Admin|Super Admin')->group(function () {
            // User Management (New Journal Manager Dashboard)
            Route::controller(JournalUserManagementController::class)->prefix('users')->name('users.')->group(function () {
                // 1. All Users
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{user}/edit', 'edit')->name('edit');
                Route::put('/{user}', 'update')->name('update');
                Route::delete('/{user}', 'destroy')->name('destroy');
                Route::post('/{user}/login-as', 'loginAs')->name('login-as');
                Route::post('/{user}/disable', 'disable')->name('disable');
                Route::post('/{user}/enable', 'enable')->name('enable');
                Route::post('/{user}/email', 'email')->name('email');

                // 2. Roles
                Route::get('/roles', 'roles')->name('roles');
                Route::get('/roles/create', 'createRole')->name('roles.create');
                Route::post('/roles', 'storeRole')->name('roles.store');
                Route::get('/roles/{role}/edit', 'editRole')->name('roles.edit');
                Route::put('/roles/{role}', 'updateRole')->name('roles.update');
                Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
                Route::post('/roles/{role}/reset', 'resetRolePermissions')->name('roles.reset');

                // 3. Site Access Options
                Route::get('/access', 'access')->name('access');
                Route::post('/access', 'updateAccess')->name('access.update');
            });

            // Journal Settings
            Route::get('/settings', [JournalController::class, 'edit'])->name('settings');
            Route::put('/settings', [JournalController::class, 'update'])->name('settings.update');
            Route::post('/settings/options', [JournalController::class, 'updateSettings'])->name('settings.options');

            // Sections
            Route::resource('sections', SectionController::class)->names([
                'index' => 'sections.index',
                'create' => 'sections.create',
                'store' => 'sections.store',
                'show' => 'sections.show',
                'edit' => 'sections.edit',
                'update' => 'sections.update',
                'destroy' => 'sections.destroy',
            ]);
            Route::post('/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
        });
    });
});
