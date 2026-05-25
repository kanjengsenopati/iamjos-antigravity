<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\JournalSelectController;
use App\Http\Controllers\Admin\RegisterController;
use App\Http\Controllers\EditorDecisionController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\ReviewWorkflowController;
use App\Http\Controllers\SubmissionFileController;
use App\Http\Controllers\Admin\SiteAdminController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\SocialAuthController;
use App\Http\Controllers\WorkflowSettingsController;
use App\Http\Controllers\SubmissionWorkflowController;
use App\Http\Controllers\Journal\PublicationController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\SubmissionDiscussionController;
use App\Http\Controllers\Journal\JournalHomepageController;
use App\Http\Controllers\Journal\WebsiteSettingsController;
use App\Http\Controllers\Journal\ProductionWorkflowController;
use App\Http\Controllers\Admin\JournalUserManagementController;
use App\Http\Controllers\Admin\Tools\CrossrefExportController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Public\JatsXmlController;
use App\Http\Controllers\Public\LockssController;
use App\Http\Controllers\Admin\PublicPageController;

// =====================================================
// OJS PREFIX CONFIGURATION
// =====================================================
$ojsPrefixEnabled = false;
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
        $ojsPrefixEnabled = (bool) \App\Facades\Settings::site('use_ojs_url_format', false);
    }
} catch (\Exception $e) {}

// =====================================================
// ROUTE DEFINITIONS
// =====================================================

$registerAllRoutes = function ($prefix = '') {
    Route::prefix($prefix)->group(function () {
        // 1. INSTALLER WIZARD
        Route::prefix('install')->name('install.')->middleware(['web', 'check_installed'])->group(function () {
            Route::get('/', [InstallController::class, 'index'])->name('index');
            Route::get('/step-2', [InstallController::class, 'step2'])->name('step2');
            Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('test-database');
            Route::get('/step-3', [InstallController::class, 'step3'])->name('step3');
            Route::post('/test-smtp', [InstallController::class, 'testSmtp'])->name('test-smtp');
            Route::get('/step-4', [InstallController::class, 'step4'])->name('step4');
            Route::post('/execute', [InstallController::class, 'execute'])->name('execute');
        });

        // 2. PORTAL HOME
Route::get('/', [PortalController::class, 'index'])->name('portal.home');
        Route::get('/search', [PortalController::class, 'search'])->name('portal.search');
        Route::get('/journals', [PortalController::class, 'journals'])->name('portal.journals');
        Route::get('/about', [PortalController::class, 'about'])->name('portal.about');
        Route::get('/page/{slug}', [PortalController::class, 'page'])->name('site.page');
        Route::get('/files/{file}/download', [SubmissionFileController::class, 'download'])->name('files.download');
        Route::get('/files/{file}/preview', [SubmissionFileController::class, 'preview'])->name('files.preview')->middleware('auth');
        Route::get('/files/{file}/serve', [SubmissionFileController::class, 'serve'])->name('files.serve');
        Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

        // 3. AUTH ROUTES (Global)
        Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
        Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate')->middleware('guest');
        Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/register', [RegisterController::class, 'create'])->name('register')->middleware('guest');
        Route::post('/register', [RegisterController::class, 'store'])->middleware('guest');
        Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'post'])->name('success-forgot-password');
        Route::get('/change-password', [ForgotPasswordController::class, 'changePassword'])->name('change-password');
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');
        Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

        // 4. PROTECTED GLOBAL ROUTES
        Route::middleware(['auth'])->group(function () {
            Route::get('/dashboard', [JournalSelectController::class, 'redirectToDashboard'])->name('dashboard');
            Route::get('/select-journal', [JournalSelectController::class, 'index'])->name('journal.select');
            Route::get('/select-journal/{journal:slug}', [JournalSelectController::class, 'select'])->name('journal.select.go');
            Route::get('/profile', function () {
                $journal = \App\Models\Journal::first();
                if ($journal) {
                    return redirect()->route('journal.profile.edit', $journal->slug);
                }
                return redirect('/');
            })->name('profile.edit');
            Route::post('/profile/upload-image', [ProfileController::class, 'uploadImage'])->name('profile.upload.image');

            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [NotificationController::class, 'index'])->name('index');
                Route::get('/{id}/read', [NotificationController::class, 'read'])->name('read');
                Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
                Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
                Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
                Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            });

            Route::get('/submissions/{submission}/log-history', [\App\Http\Controllers\Admin\SubmissionController::class, 'logHistory'])->name('submission.log.history');
            Route::post('/{journal:slug}/submissions/{submission}/notes', [\App\Http\Controllers\Admin\SubmissionController::class, 'storeNote'])->name('submission.notes.store');
            Route::delete('/{journal:slug}/submissions/{submission}/notes/{note}', [\App\Http\Controllers\Admin\SubmissionController::class, 'destroyNote'])->name('submission.notes.destroy');
        });

        // 5. GLOBAL ADMIN ROUTES
        Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Super Admin'])->group(function () {
            Route::get('/', [SiteAdminController::class, 'index'])->name('site.index');
            Route::get('/site-setting', [SiteAdminController::class, 'siteSettings'])->name('site.settings.form');
            Route::get('/system-informations', [SiteAdminController::class, 'systemInfo'])->name('site.system-info');
            Route::post('/settings', [SiteAdminController::class, 'updateSettings'])->name('site.settings.update');
            Route::post('/expire-sessions', [SiteAdminController::class, 'expireSessions'])->name('site.expire-sessions');
            Route::post('/clear-cache', [SiteAdminController::class, 'clearDataCache'])->name('site.clear-cache');
            Route::post('/clear-templates', [SiteAdminController::class, 'clearTemplateCache'])->name('site.clear-templates');
            Route::post('/clear-logs', [SiteAdminController::class, 'clearScheduledTaskLogs'])->name('site.clear-logs');

            Route::get('/system-settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('system-settings.index');
            Route::post('/system-settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('system-settings.update');


            Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
            Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
            Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
            Route::get('/journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
            Route::put('/journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
            Route::delete('/journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');

            Route::controller(\App\Http\Controllers\Admin\SiteAppearanceController::class)->prefix('site-appearance')->name('site.appearance.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/reorder', 'reorder')->name('reorder');
                Route::post('/{block}/toggle', 'toggle')->name('toggle');
                Route::get('/{block}/edit', 'edit')->name('edit');
                Route::put('/{block}', 'update')->name('update');
                Route::get('/{block}/config', 'getConfig')->name('config');
                Route::put('/{block}/config', 'updateConfig')->name('config.update');
                Route::post('/{block}/reset', 'reset')->name('reset');
                Route::delete('/{block}/logo', 'deleteLogo')->name('logo.delete');
            });

            Route::controller(\App\Http\Controllers\Admin\SitePageController::class)->prefix('site-pages')->name('site-pages.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{sitePage}', 'show')->name('show');
                Route::get('/{sitePage}/edit', 'edit')->name('edit');
                Route::put('/{sitePage}', 'update')->name('update');
                Route::delete('/{sitePage}', 'destroy')->name('destroy');
                Route::post('/{sitePage}/toggle', 'toggle')->name('toggle');
                Route::post('/{sitePage}/duplicate', 'duplicate')->name('duplicate');
                Route::post('/reorder', 'reorder')->name('reorder');
                Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            });

            // Navigation Menu CRUD API Routes
            Route::controller(\App\Http\Controllers\Admin\NavigationMenuController::class)->prefix('api/navigation-menus')->name('api.navigation-menus.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{navigationMenu}', 'show')->name('show');
                Route::put('/{navigationMenu}', 'update')->name('update');
                Route::delete('/{navigationMenu}', 'destroy')->name('destroy');
                Route::post('/{navigationMenu}/reorder', 'reorder')->name('reorder');
            });

            // Navigation Menu Item CRUD API Routes
            Route::controller(\App\Http\Controllers\Admin\NavigationMenuItemController::class)->prefix('api/navigation-menu-items')->name('api.navigation-menu-items.')->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{navigationMenuItem}', 'update')->name('update');
                Route::delete('/{navigationMenuItem}', 'destroy')->name('destroy');
            });

            // Unified Public Page Management
            Route::get('/public-page', [PublicPageController::class, 'index'])->name('public-page.index');

            Route::controller(\App\Http\Controllers\Admin\SiteNavigationController::class)->prefix('site-navigation')->name('site-navigation.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/items', 'storeItem')->name('items.store');
                Route::put('/items/{item}', 'updateItem')->name('items.update');
                Route::delete('/items/{item}', 'destroyItem')->name('items.destroy');
                Route::post('/items/reorder', 'reorderItems')->name('items.reorder');
            });

            Route::controller(\App\Http\Controllers\Admin\PublicPageController::class)->prefix('public-page')->name('public-page.')->group(function () {
                Route::get('/', 'index')->name('index');
            });

            Route::controller(\App\Http\Controllers\MalwareGuardController::class)->prefix('malware')->name('malware.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/status', 'status')->name('status');
                Route::post('/scan/init', 'initScan')->name('scan.init');
                Route::post('/scan/process', 'processBatch')->name('scan.process');
                Route::post('/scan/cancel', 'cancel')->name('scan.cancel');
                Route::post('/reset', 'reset')->name('reset');
                Route::post('/{finding}/ignore', 'ignore')->name('ignore');
            });

            Route::controller(\App\Http\Controllers\Admin\Tools\OjsMigrationController::class)->prefix('tools/migration')->name('tools.migration.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/upload', 'upload')->name('upload');
                Route::post('/run', 'runStep')->name('run');
                Route::post('/reset', 'reset')->name('reset');
                Route::post('/reset-progress', 'resetProgress')->name('reset-progress');
                Route::post('/reset-articles', 'resetArticles')->name('reset-articles');
                Route::post('/reset-issues', 'resetIssues')->name('reset-issues');
                Route::post('/reset-journals', 'resetJournals')->name('reset-journals');
                Route::post('/reset-journal/{journal}', 'resetJournal')->name('reset-journal');
                Route::get('/details/{journal}', 'getJournalDetails')->name('details');
                Route::post('/reset-items', 'resetSelectedItems')->name('reset-items');
            });

            Route::controller(\App\Http\Controllers\Admin\FileManagerController::class)->prefix('file-manager')->name('file-manager.')->group(function () {
                Route::get('/list', 'list')->name('list');
                Route::post('/upload', 'upload')->name('upload');
                Route::post('/create-folder', 'createFolder')->name('create-folder');
                Route::post('/delete', 'delete')->name('delete');
            });
        });

        // 6. TRANSLATE & OAI
        Route::get('translate', [TranslateController::class, 'index'])->name('translate');
        Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');
        Route::get('oai/stylesheet', function () {
            return response()->file(public_path('oai.xsl'), ['Content-Type' => 'text/xsl']);
        });

        // 7. JOURNAL PUBLIC ROUTES
        Route::prefix('{journal}')->group(function () {
            Route::any('oai', [\App\Http\Controllers\Public\OaiController::class, 'handle'])->middleware('throttle:60,1')->name('journal.oai');
            Route::get('lockss', [\App\Http\Controllers\Public\LockssController::class, 'manifest'])->name('journal.lockss.manifest');
            Route::get('clockss', [\App\Http\Controllers\Public\LockssController::class, 'clockssManifest'])->name('journal.clockss.manifest');

            Route::get('/', [\App\Http\Controllers\Journal\JournalHomepageController::class, 'index'])->name('journal.public.home');
            Route::get('/current', [\App\Http\Controllers\PublicController::class, 'currentIssue'])->name('journal.public.current');
            Route::get('/archives', [\App\Http\Controllers\PublicController::class, 'archives'])->name('journal.public.archives');
            Route::get('/about', [\App\Http\Controllers\PublicController::class, 'about'])->name('journal.public.about');
            Route::get('/announcement', [\App\Http\Controllers\PublicController::class, 'announcements'])->name('journal.announcement.index');
            Route::get('/announcement/{id}', [\App\Http\Controllers\PublicController::class, 'announcement'])->name('journal.announcement.show');
            Route::get('/information/readers', [\App\Http\Controllers\PublicController::class, 'infoReaders'])->name('journal.info.readers');
            Route::get('/information/authors', [\App\Http\Controllers\PublicController::class, 'infoAuthors'])->name('journal.info.authors');
            Route::get('/information/librarians', [\App\Http\Controllers\PublicController::class, 'infoLibrarians'])->name('journal.info.librarians');
            Route::get('/author-guidelines', [\App\Http\Controllers\PublicController::class, 'authorGuidelines'])->name('journal.public.author-guidelines');
            Route::get('/editorial-team', [\App\Http\Controllers\PublicController::class, 'editorialTeam'])->name('journal.public.editorial-team');
            Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('journal.public.search');
            Route::get('/search/quick', [\App\Http\Controllers\SearchController::class, 'quickSearch'])->name('journal.public.search.quick');
            Route::get('/issue/view/{issue}', [\App\Http\Controllers\PublicController::class, 'issue'])->name('journal.public.issue');
            Route::get('/issue/{issue}', [\App\Http\Controllers\PublicController::class, 'issue'])->name('journal.public.issue.legacy');
            Route::get('/page/{path}', [\App\Http\Controllers\PublicController::class, 'customPage'])->name('journal.custom-page');
            Route::get('/article/view/{article}', [\App\Http\Controllers\PublicController::class, 'article'])->name('journal.public.article');
            Route::get('/article/{article}/view', [\App\Http\Controllers\PublicController::class, 'articleReader'])->name('journal.public.article.reader');
            Route::get('/article/{article}/view-legacy', [\App\Http\Controllers\PublicController::class, 'article'])->name('journal.article.view');
            Route::get('/article/{article}', [\App\Http\Controllers\PublicController::class, 'article'])->name('journal.public.article.legacy');
            Route::get('/article/view/{article}/{galley}', [\App\Http\Controllers\PublicController::class, 'viewGalley'])->name('journal.article.galley');
            Route::get('/article/{article}/galley/{galley}/download', [\App\Http\Controllers\PublicController::class, 'downloadGalley'])->name('journal.article.download');
            Route::get('/article/download/{seq_id}/{filename}.pdf', [\App\Http\Controllers\PublicController::class, 'downloadPdf'])->name('journal.article.download.pdf');
            Route::get('/article/{article}/citation/ris', [\App\Http\Controllers\PublicController::class, 'exportCitationRIS'])->name('citation.ris');
            Route::get('/article/{article}/citation/bibtex', [\App\Http\Controllers\PublicController::class, 'exportCitationBibTeX'])->name('citation.bibtex');
            Route::get('/article/{article}/jats', [JatsXmlController::class, 'article'])->name('journal.article.jats');

            Route::middleware('guest')->group(function () {
                Route::get('/register', [\App\Http\Controllers\JournalRegisterController::class, 'showRegistrationForm'])->name('journal.register');
                Route::post('/register', [\App\Http\Controllers\JournalRegisterController::class, 'register'])->name('journal.register.store');
            });
        });

        // 8. JOURNAL DASHBOARD & AUTH
        Route::prefix('{journal}')->group(function () {
            Route::middleware(['journal.detect'])->group(function () {
                Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'index'])->name('journal.login')->middleware('guest');
                Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'authenticate'])->name('journal.authenticate')->middleware('guest');
                Route::match(['get', 'post'], '/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('journal.logout');
                Route::get('/auth/google', [\App\Http\Controllers\Admin\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google.journal');
            });

            Route::middleware(['auth', 'journal.context'])->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('journal.dashboard');
                Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('journal.profile.edit');
                Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('journal.profile.update');
                Route::patch('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('journal.profile.password');
                Route::patch('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('journal.profile.avatar');
                Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('journal.profile.avatar.delete');
                Route::put('/profile/roles', [\App\Http\Controllers\ProfileController::class, 'updateRoles'])->name('journal.profile.roles.update');
                Route::post('/profile/upload-image', [\App\Http\Controllers\ProfileController::class, 'uploadImage'])->name('journal.profile.upload.image');
                Route::post('/enroll', [\App\Http\Controllers\ProfileController::class, 'enroll'])->name('journal.enroll');
                
                Route::post('/users/stop-impersonating', [JournalUserManagementController::class, 'stopImpersonating'])->name('journal.users.stop-impersonating');
                
                Route::middleware('role:Journal Manager|Editor|Admin|Super Admin')->controller(JournalUserManagementController::class)->prefix('users')->name('journal.users.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/enroll', 'enrollForm')->name('enroll');
                    Route::post('/enroll', 'enroll')->name('enroll.store');
                    Route::get('/{user}/edit', 'edit')->name('edit');
                    Route::put('/{user}', 'update')->name('update');
                    Route::delete('/{user}', 'destroy')->name('destroy');
                    Route::post('/{user}/login-as', 'loginAs')->name('login-as');
                    Route::post('/{user}/disable', 'disable')->name('disable');
                    Route::post('/{user}/enable', 'enable')->name('enable');
                    Route::post('/{user}/email', 'email')->name('email');
                    Route::get('/{user}/merge', 'merge')->name('merge');
                    Route::post('/{user}/merge', 'executeMerge')->name('execute-merge');
                    Route::get('/roles', 'roles')->name('roles');
                    Route::get('/roles/create', 'createRole')->name('roles.create');
                    Route::post('/roles', 'storeRole')->name('roles.store');
                    Route::get('/roles/{role}/edit', 'editRole')->name('roles.edit');
                    Route::put('/roles/{role}', 'updateRole')->name('roles.update');
                    Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
                    Route::post('/roles/{role}/reset', 'resetRolePermissions')->name('roles.reset');
                    Route::post('/roles/{role}/toggle-permission', 'updateRolePermission')->name('roles.toggle-permission');
                    Route::get('/access', 'access')->name('access');
                    Route::post('/access', 'updateAccess')->name('access.update');
                    Route::get('/notify', 'notify')->name('notify');
                    Route::post('/notify', 'sendNotification')->name('notify.send');
                });

                Route::resource('submissions', SubmissionController::class)->names([
                    'index' => 'journal.submissions.index',
                    'create' => 'journal.submissions.create',
                    'store' => 'journal.submissions.store',
                    'show' => 'journal.submissions.show',
                    'edit' => 'journal.submissions.edit',
                    'update' => 'journal.submissions.update',
                    'destroy' => 'journal.submissions.destroy',
                ]);

                Route::post('/submissions/{submission}/files', [SubmissionFileController::class, 'store'])->name('journal.submissions.files.store');
                Route::delete('/files/{file}', [SubmissionFileController::class, 'destroy'])->name('journal.files.destroy');
                Route::post('/upload/image', [SubmissionController::class, 'uploadImage'])->name('journal.upload.image');
                Route::post('/discussion/upload-image', [SubmissionDiscussionController::class, 'uploadCkeditorImage'])->name('journal.discussion.upload-image');
                Route::post('/discussion/upload-file', [SubmissionDiscussionController::class, 'uploadDiscussionFile'])->name('journal.discussion.upload-file');
                Route::get('/discussion/file/{file}', [SubmissionDiscussionController::class, 'download'])->name('journal.discussion.file.download');
                Route::post('/{submission}/discussion/create', [SubmissionDiscussionController::class, 'store'])->name('journal.discussion.create');
                Route::post('/{submission}/discussion/{discussion}/reply', [SubmissionDiscussionController::class, 'storeReply'])->name('journal.discussion.reply');
                Route::post('/{submission}/discussion/{discussion}/close', [SubmissionDiscussionController::class, 'close'])->name('journal.discussion.close');
                Route::post('/{submission}/discussion/{discussion}/reopen', [SubmissionDiscussionController::class, 'reopen'])->name('journal.discussion.reopen');
                Route::put('/{submission}/discussion/{discussion}/message/{message}', [SubmissionDiscussionController::class, 'updateMessage'])->name('journal.discussion.message.update');
                Route::post('/{submission}/discussion/{discussion}/read', [SubmissionDiscussionController::class, 'markAsRead'])->name('journal.discussion.read');

                Route::prefix('workflow')->name('journal.workflow.')
                ->middleware('role:Editor|Section Editor|Journal Manager|Admin|Super Admin')
                ->group(function () {
                    Route::post('/{submission}/file', [SubmissionWorkflowController::class, 'uploadFile'])->name('file.store');
                    Route::post('/{submission}/discussion', [SubmissionWorkflowController::class, 'storeDiscussion'])->name('discussion.store');
                    Route::get('/{submission}', [SubmissionWorkflowController::class, 'show'])->name('show');
                    Route::post('/{submission}/assign-editor', [SubmissionWorkflowController::class, 'assignEditor'])->name('assign-editor');
                    Route::delete('/{submission}/remove-editor/{assignment}', [SubmissionWorkflowController::class, 'removeEditor'])->name('remove-editor');
                    Route::post('/{submission}/change-stage', [SubmissionWorkflowController::class, 'changeStage'])->name('change-stage');
                    Route::post('/{submission}/schedule-publication', [SubmissionWorkflowController::class, 'schedulePublication'])->name('schedule-publication');
                    Route::get('/{submission}/assign-reviewer', [ReviewWorkflowController::class, 'assignReviewerPage'])->name('assign-reviewer-page');
                    Route::get('/reviewers/search', [ReviewWorkflowController::class, 'searchReviewers'])->name('reviewers.search');
                    Route::post('/{submission}/assign-reviewer', [ReviewWorkflowController::class, 'assignReviewer'])->name('assign-reviewer');
                    Route::delete('/{submission}/unassign-reviewer/{assignment}', [ReviewWorkflowController::class, 'unassignReviewer'])->name('unassign-reviewer');
                    Route::post('/review-assignment/{reviewAssignment}/rate', [ReviewWorkflowController::class, 'rateReviewer'])->name('review-assignment.rate');
                    Route::post('/review-assignment/{reviewAssignment}/update', [ReviewWorkflowController::class, 'updateReviewAssignment'])->name('review-assignment.update');
                    Route::post('/{submission}/record-decision', [ReviewWorkflowController::class, 'recordDecision'])->name('record-decision');
                    Route::post('/{submission}/promote-to-copyediting', [ReviewWorkflowController::class, 'promoteToCopyediting'])->name('promote-copyediting');
                    Route::post('/{submission}/send-to-production', [ReviewWorkflowController::class, 'sendToProduction'])->name('send-production');
                    Route::post('/{submission}/request-revisions', [ReviewWorkflowController::class, 'requestRevisions'])->name('request-revisions');
                    Route::get('/{submission}/reviewer-attachments', [ReviewWorkflowController::class, 'getReviewerAttachments'])->name('reviewer-attachments');
                    Route::post('/{submission}/upload-decision-file', [ReviewWorkflowController::class, 'uploadDecisionFile'])->name('upload-decision-file');
                    Route::post('/{submission}/create-new-round', [ReviewWorkflowController::class, 'createNewRound'])->name('create-new-round');
                    Route::get('/{submission}/revision-files', [ReviewWorkflowController::class, 'getRevisionFiles'])->name('revision-files');
                    Route::get('/{submission}/promotable-files', [ReviewWorkflowController::class, 'getPromotableFiles'])->name('promotable-files');
                    Route::get('/{submission}/review-stage-files', [ReviewWorkflowController::class, 'getReviewStageFiles'])->name('review-stage-files');
                    Route::post('/{submission}/copy-review-to-draft', [ReviewWorkflowController::class, 'copyReviewFilesToDraft'])->name('copy-review-to-draft');
                    Route::get('/{submission}/available-files', [SubmissionWorkflowController::class, 'getAvailableFiles'])->name('available-files');
                    Route::post('/{submission}/promote-to-review', [SubmissionWorkflowController::class, 'promoteToReview'])->name('promote-review');
                    Route::post('/{submission}/skip-review', [SubmissionWorkflowController::class, 'skipReview'])->name('skip-review');
                    Route::post('/{submission}/decline', [SubmissionWorkflowController::class, 'decline'])->name('decline');
                    Route::post('/{submission}/galley', [ProductionWorkflowController::class, 'storeGalley'])->name('galley.store');
                    Route::put('/{submission}/galley/{galley}', [ProductionWorkflowController::class, 'updateGalley'])->name('galley.update');
                    Route::delete('/{submission}/galley/{galley}', [ProductionWorkflowController::class, 'destroyGalley'])->name('galley.destroy');
                    Route::post('/{submission}/assign-issue', [ProductionWorkflowController::class, 'assignToIssue'])->name('assign-issue');
                    Route::post('/{submission}/unschedule', [ProductionWorkflowController::class, 'unschedule'])->name('unschedule');
                    Route::post('/{submission}/publish', [ProductionWorkflowController::class, 'publish'])->name('publish');
                    Route::post('/{submission}/unpublish', [ProductionWorkflowController::class, 'unpublish'])->name('unpublish');
                    Route::get('/issues', [ProductionWorkflowController::class, 'getIssues'])->name('issues.list');
                    Route::prefix('{submission}/publication')->name('publication.')->group(function () {
                        Route::get('/', [PublicationController::class, 'show'])->name('show');
                        Route::post('/title', [PublicationController::class, 'updateTitleAbstract'])->name('title.update');
                        Route::post('/metadata', [PublicationController::class, 'updateMetadata'])->name('metadata.update');
                        Route::post('/references', [PublicationController::class, 'updateReferences'])->name('references.update');
                        Route::post('/license', [PublicationController::class, 'updateLicense'])->name('license.update');
                        Route::post('/issue', [PublicationController::class, 'assignIssue'])->name('issue.assign');
                        Route::post('/unschedule', [PublicationController::class, 'unschedule'])->name('unschedule');
                        Route::post('/publish', [PublicationController::class, 'publish'])->name('publish');
                        Route::post('/unpublish', [PublicationController::class, 'unpublish'])->name('unpublish');
                        Route::post('/contributor', [PublicationController::class, 'storeContributor'])->name('contributor.store');
                        Route::put('/contributor/{author}', [PublicationController::class, 'updateContributor'])->name('contributor.update');
                        Route::delete('/contributor/{author}', [PublicationController::class, 'destroyContributor'])->name('contributor.destroy');
                        Route::post('/contributors/reorder', [PublicationController::class, 'reorderContributors'])->name('contributors.reorder');
                        Route::post('/doi/assign', [PublicationController::class, 'assignDoi'])->name('doi.assign');
                        Route::post('/doi/clear', [PublicationController::class, 'clearDoi'])->name('doi.clear');
                        Route::post('/doi/suffix', [PublicationController::class, 'updateDoiSuffix'])->name('doi.suffix');
                        Route::get('/sections', [PublicationController::class, 'getSections'])->name('sections.list');
                    });
                    Route::get('/{submission}/jats', [JatsXmlController::class, 'workflowPreview'])->name('jats');
                });

                Route::prefix('editorial')->name('journal.editorial.')->middleware('role:Editor|Admin|Super Admin')->group(function () {
                    Route::get('/queue', [EditorialController::class, 'queue'])->name('queue');
                    Route::get('/archives', [EditorialController::class, 'archives'])->name('archives');
                    Route::post('/{submission}/assign', [EditorialController::class, 'assign'])->name('assign');
                    Route::post('/{submission}/accept', [EditorialController::class, 'accept'])->name('accept');
                    Route::post('/{submission}/reject', [EditorialController::class, 'reject'])->name('reject');
                    Route::post('/{submission}/revision', [EditorialController::class, 'requestRevision'])->name('revision');
                });

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

                Route::prefix('reviewer')->name('journal.reviewer.')->middleware('role:Reviewer|Editor|Admin|Super Admin')->group(function () {
                    Route::get('/', [ReviewerController::class, 'index'])->name('index');
                    Route::get('/{identifier}', [ReviewerController::class, 'show'])->name('show');
                    Route::post('/{assignment}/accept', [ReviewerController::class, 'accept'])->name('accept');
                    Route::post('/{assignment}/decline', [ReviewerController::class, 'decline'])->name('decline');
                    Route::post('/{assignment}/submit', [ReviewerController::class, 'submit'])->name('submit');
                    Route::post('/{assignment}/upload-attachment', [ReviewerController::class, 'uploadAttachment'])->name('upload-attachment');
                    Route::delete('/{assignment}/attachment/{file}', [ReviewerController::class, 'deleteAttachment'])->name('delete-attachment');
                });

                Route::prefix('editor')->name('journal.editor.')->middleware('role:Editor|Admin|Super Admin')->group(function () {
                    Route::get('/submission/{submission}', [EditorDecisionController::class, 'show'])->name('show');
                    Route::post('/submission/{submission}/assign-reviewer', [EditorDecisionController::class, 'assignReviewer'])->name('assign-reviewer');
                    Route::delete('/reviewer/{assignment}', [EditorDecisionController::class, 'cancelReviewer'])->name('cancel-reviewer');
                    Route::post('/submission/{submission}/decision', [EditorDecisionController::class, 'recordDecision'])->name('decision');
                    Route::post('/submission/{submission}/send-to-review', [EditorDecisionController::class, 'sendToReview'])->name('send-to-review');
                });

                Route::middleware('role:Journal Manager|Editor|Admin|Super Admin')->prefix('announcements')->name('journal.announcements.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Journal\AnnouncementController::class, 'index'])->name('index');
                    Route::post('/', [\App\Http\Controllers\Journal\AnnouncementController::class, 'store'])->name('store');
                    Route::get('/{announcement}/edit', [\App\Http\Controllers\Journal\AnnouncementController::class, 'edit'])->name('edit');
                    Route::put('/{announcement}', [\App\Http\Controllers\Journal\AnnouncementController::class, 'update'])->name('update');
                    Route::delete('/{announcement}', [\App\Http\Controllers\Journal\AnnouncementController::class, 'destroy'])->name('destroy');
                    Route::post('/{announcement}/toggle', [\App\Http\Controllers\Journal\AnnouncementController::class, 'toggleActive'])->name('toggle');
                });

                Route::middleware('role:Journal Manager|Editor|Admin|Super Admin')->prefix('settings')->name('journal.settings.')->group(function () {
                    Route::get('/', [JournalController::class, 'settings'])->name('index');
                    Route::put('/', [JournalController::class, 'updateSettings'])->name('update');
                    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
                    Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
                    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
                    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
                    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
                    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
                    Route::prefix('workflow')->name('workflow.')->group(function () {
                        Route::get('/', [WorkflowSettingsController::class, 'index'])->name('index');
                        Route::put('/', [WorkflowSettingsController::class, 'updateSettings'])->name('update');
                        Route::post('/checklists', [WorkflowSettingsController::class, 'storeChecklist'])->name('checklists.store');
                        Route::put('/checklists/{checklist}', [WorkflowSettingsController::class, 'updateChecklist'])->name('checklists.update');
                        Route::delete('/checklists/{checklist}', [WorkflowSettingsController::class, 'destroyChecklist'])->name('checklists.destroy');
                        Route::post('/review-forms', [WorkflowSettingsController::class, 'storeReviewForm'])->name('review-forms.store');
                        Route::put('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'updateReviewForm'])->name('review-forms.update');
                        Route::delete('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'destroyReviewForm'])->name('review-forms.destroy');
                        Route::post('/library', [WorkflowSettingsController::class, 'storeLibraryFile'])->name('library.store');
                        Route::get('/library/{libraryFile}/download', [WorkflowSettingsController::class, 'downloadLibraryFile'])->name('library.download');
                        Route::delete('/library/{libraryFile}', [WorkflowSettingsController::class, 'destroyLibraryFile'])->name('library.destroy');
                        Route::put('/email-templates/{emailTemplate}', [WorkflowSettingsController::class, 'updateEmailTemplate'])->name('email-templates.update');
                        Route::post('/email-templates/{emailTemplate}/toggle', [WorkflowSettingsController::class, 'toggleEmailTemplate'])->name('email-templates.toggle');
                        Route::post('/email-templates/{emailTemplate}/reset', [WorkflowSettingsController::class, 'resetEmailTemplate'])->name('email-templates.reset');
                        Route::put('/notification-templates/{eventKey}', [WorkflowSettingsController::class, 'updateNotificationTemplate'])->name('notification-templates.update');
                        Route::post('/whatsapp-toggle', [WorkflowSettingsController::class, 'toggleWhatsappNotifications'])->name('whatsapp.toggle');
                    });
                    Route::controller(\App\Http\Controllers\Admin\DistributionSettingsController::class)->prefix('distribution')->name('distribution.')->group(function () {
                        Route::get('/', 'edit')->name('edit');
                        Route::put('/', 'update')->name('update');
                    });
                    Route::controller(WebsiteSettingsController::class)->prefix('website')->name('website.')->group(function () {
                        Route::get('/', 'edit')->name('edit');
                        Route::put('/', 'update')->name('update');
                        Route::delete('/indexed-image', 'deleteIndexedImage')->name('indexed-image.delete');
                        Route::delete('/favicon', 'deleteFavicon')->name('favicon.delete');
                    });
                    Route::controller(\App\Http\Controllers\Journal\DoiSettingsController::class)->prefix('doi')->name('doi.')->group(function () {
                        Route::get('/', 'edit')->name('edit');
                        Route::put('/', 'update')->name('update');
                        Route::post('/reassign', 'reassign')->name('reassign');
                        Route::post('/preview', 'preview')->name('preview');
                    });
                    Route::controller(\App\Http\Controllers\Journal\NavigationController::class)->prefix('navigation')->name('navigation.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/menus', 'storeMenu')->name('menus.store');
                        Route::put('/menus/{menu}', 'updateMenu')->name('menus.update');
                        Route::delete('/menus/{menu}', 'destroyMenu')->name('menus.destroy');
                        Route::get('/items/create', 'createItem')->name('items.create');
                        Route::post('/items', 'storeItem')->name('items.store');
                        Route::get('/items/{item}/edit', 'editItem')->name('items.edit');
                        Route::put('/items/{item}', 'updateItem')->name('items.update');
                        Route::delete('/items/{item}', 'destroyItem')->name('items.destroy');
                        Route::post('/assign', 'assignItem')->name('assign');
                        Route::delete('/unassign/{assignment}', 'unassignItem')->name('unassign');
                        Route::post('/move-up/{assignment}', 'moveUp')->name('move-up');
                        Route::post('/move-down/{assignment}', 'moveDown')->name('move-down');
                        Route::post('/reorder', 'reorderItems')->name('reorder');
                    });
                    Route::controller(\App\Http\Controllers\Journal\SidebarController::class)->prefix('sidebar')->name('sidebar.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'store')->name('store');
                        Route::put('/{block}', 'update')->name('update');
                        Route::delete('/{block}', 'destroy')->name('destroy');
                        Route::post('/{block}/toggle', 'toggle')->name('toggle');
                        Route::post('/reorder', 'reorder')->name('reorder');
                        Route::post('/system-block', 'addSystemBlock')->name('system-block');
                    });




                    Route::controller(\App\Http\Controllers\Admin\Stats\ArticleStatsController::class)->prefix('statistics')->name('statistics.')->group(function () {
                        Route::get('/articles', 'index')->name('articles');
                        Route::get('/articles/data', 'getData')->name('articles.data');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Stats\EditorialStatsController::class)->prefix('statistics')->name('statistics.')->group(function () {
                        Route::get('/editorial', 'index')->name('editorial');
                        Route::get('/editorial/data', 'getData')->name('editorial.data');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Stats\UserStatsController::class)->prefix('statistics')->name('statistics.')->group(function () {
                        Route::get('/users', 'index')->name('users');
                        Route::get('/users/data', 'getData')->name('users.data');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Reports\ReportController::class)->prefix('statistics')->name('statistics.')->group(function () {
                        Route::get('/reports', 'index')->name('reports');
                        Route::post('/reports/preview', 'preview')->name('reports.preview');
                        Route::post('/reports/export', 'export')->name('reports.export');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Stats\CounterStatsController::class)->prefix('statistics')->name('statistics.')->group(function () {
                        Route::get('/counter', 'index')->name('counter');
                        Route::get('/counter/tr', 'titleReport')->name('counter.tr');
                        Route::get('/counter/ir', 'itemReport')->name('counter.ir');
                        Route::get('/counter/ir/csv', 'exportCsv')->name('counter.ir.csv');
                    });
                    Route::controller(\App\Http\Controllers\Journal\ScholarMonitorController::class)->prefix('stats/scholar')->name('stats.scholar.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/{submission}/check', 'check')->name('check');
                        Route::put('/{submission}', 'update')->name('update');
                    });
                    Route::controller(\App\Http\Controllers\Admin\ToolsController::class)->prefix('tools')->name('tools.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/permissions/reset', 'resetPermissions')->name('permissions.reset');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\ImportController::class)->prefix('tools/import/oai')->name('tools.import.oai.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/preview', 'preview')->name('preview');
                        Route::post('/harvest', 'harvest')->name('harvest');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\NativeImportExportController::class)->prefix('tools/importexport/native')->name('tools.native.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/import', 'import')->name('import');
                        Route::post('/export/articles', 'exportArticles')->name('export.articles');
                        Route::post('/export/issues', 'exportIssues')->name('export.issues');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\CopernicusExportController::class)->prefix('tools/importexport/copernicus')->name('tools.copernicus.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::match(['get', 'post'], '/export/articles', 'exportArticles')->name('export.articles');
                        Route::match(['get', 'post'], '/export/issues', 'exportIssues')->name('export.issues');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\UserImportExportController::class)->prefix('tools/importexport/users')->name('tools.users.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/import', 'import')->name('import');
                        Route::post('/export', 'export')->name('export');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\PubmedExportController::class)->prefix('tools/importexport/pubmed')->name('tools.pubmed.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/export', 'export')->name('export');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\DoajExportController::class)->prefix('tools/importexport/doaj')->name('tools.doaj.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/export', 'export')->name('export');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\DataciteExportController::class)->prefix('tools/importexport/datacite')->name('tools.datacite.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/export', 'export')->name('export');
                    });
                    Route::controller(\App\Http\Controllers\Admin\Tools\QuickSubmitController::class)->prefix('tools/quicksubmit')->name('tools.quicksubmit.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/store', 'store')->name('store');
                    });
                    Route::prefix('tools/importexport/crossref')->name('tools.crossref.')->group(function () {
                        Route::get('/', [\App\Http\Controllers\Admin\Tools\CrossrefExportController::class, 'index'])->name('index');
                        Route::post('/download', [\App\Http\Controllers\Admin\Tools\CrossrefExportController::class, 'export'])->name('download');
                        Route::post('/save', [\App\Http\Controllers\Admin\Tools\CrossrefExportController::class, 'saveSettings'])->name('save');
                        Route::post('/deposit', [\App\Http\Controllers\Admin\Tools\CrossrefExportController::class, 'deposit'])->name('deposit');
                        Route::post('/mark-active', [\App\Http\Controllers\Admin\Tools\CrossrefExportController::class, 'markActive'])->name('mark_active');
                    });
                });

                Route::prefix('admin')->name('journal.admin.')->middleware('role:Admin|Super Admin')->group(function () {
                    Route::controller(JournalUserManagementController::class)->prefix('users')->name('users.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/', 'store')->name('store');
                        Route::get('/enroll', 'enrollForm')->name('enroll');
                        Route::post('/enroll', 'enroll')->name('enroll.store');
                        Route::get('/{user}/edit', 'edit')->name('edit');
                        Route::put('/{user}', 'update')->name('update');
                        Route::delete('/{user}', 'destroy')->name('destroy');
                        Route::post('/{user}/login-as', 'loginAs')->name('login-as');
                        Route::post('/{user}/disable', 'disable')->name('disable');
                        Route::post('/{user}/enable', 'enable')->name('enable');
                        Route::post('/{user}/email', 'email')->name('email');
                        Route::get('/{user}/merge', 'merge')->name('merge');
                        Route::post('/{user}/merge', 'executeMerge')->name('execute-merge');
                        Route::get('/roles', 'roles')->name('roles');
                        Route::get('/roles/create', 'createRole')->name('roles.create');
                        Route::post('/roles', 'storeRole')->name('roles.store');
                        Route::get('/roles/{role}/edit', 'editRole')->name('roles.edit');
                        Route::put('/roles/{role}', 'updateRole')->name('roles.update');
                        Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
                        Route::post('/roles/{role}/reset', 'resetRolePermissions')->name('roles.reset');
                        Route::post('/roles/{role}/toggle-permission', 'updateRolePermission')->name('roles.toggle-permission');
                        Route::get('/access', 'access')->name('access');
                        Route::post('/access', 'updateAccess')->name('access.update');
                        Route::get('/notify', 'notify')->name('notify');
                        Route::post('/notify', 'sendNotification')->name('notify.send');
                    });
                    Route::get('/settings', [JournalController::class, 'edit'])->name('settings');
                    Route::put('/settings', [JournalController::class, 'update'])->name('settings.update');
                    Route::post('/settings/options', [JournalController::class, 'updateSettings'])->name('settings.options');
                    Route::resource('sections', SectionController::class)->names([
                        'index' => 'sections.index', 'create' => 'sections.create', 'store' => 'sections.store', 'show' => 'sections.show', 'edit' => 'sections.edit', 'update' => 'sections.update', 'destroy' => 'sections.destroy',
                    ]);
                    Route::post('/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
                });
            });
        });
    });
};

// =====================================================
// EXECUTE ROUTE REGISTRATION
// =====================================================

// Always register WITHOUT prefix first (for redirection)
if ($ojsPrefixEnabled) {
    Route::middleware([\App\Http\Middleware\EnforceOjsPrefix::class])->group(function () use ($registerAllRoutes) {
        $registerAllRoutes('');
    });
} else {
    $registerAllRoutes('');
}

// Register WITH index.php prefix last (overwrites names)
if ($ojsPrefixEnabled) {
    $registerAllRoutes('index.php');
}
