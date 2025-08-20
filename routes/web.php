<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BpdController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\HomeAdsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\HomeMemberController;
use App\Http\Controllers\Admin\HomeSectorController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\HomePartnerController;
use App\Http\Controllers\Admin\MediaCornerController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\AboutUsHistoryController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\HonoraryCouncilController;
use App\Http\Controllers\Admin\HomeDocumentationController;
use App\Http\Controllers\Admin\AboutUsInformationController;
use App\Http\Controllers\Admin\DirectionCommitmentController;
use App\Http\Controllers\Admin\RegionalCoordinatorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'post'])->name('success-forgot-password');
Route::get('/change-password', [ForgotPasswordController::class, 'changePassword'])->name('change-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Route::get('translate', [TranslateController::class, 'index'])->name('translate');
// Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');
// Route::get('translate/chinese', [TranslateController::class, 'translateChinese'])->name('translate.chinese');
// Route::post('translate_post/chinese', [TranslateController::class, 'translateChinesePost'])->name('translate_post.chinese');
// Route::post('translate_name/chinese', [TranslateController::class, 'translateChineseName'])->name('translate_name.chinese');
// Route::resource('log_activity', LogActivityController::class, ['only' => ['index', 'show']]);


Route::resource('admin', AdminController::class);
Route::resource('permission', PermissionController::class);
Route::resource('role', RoleController::class);
//end auth
Route::group(['middleware' => ['auth',]], function () {
    Route::get('edit-profile-admin', [AdminController::class, 'editProfile'])
        ->name('profile-admin.edit')->withoutMiddleware('permission:admin');
    Route::put('edit-profile-admin', [AdminController::class, 'updateProfile'])
        ->name('profile-admin.update')->withoutMiddleware('permission:admin');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::resource('home-slider', HomeSliderController::class);
    Route::resource('home-member', HomeMemberController::class);
    Route::resource('home-sector', HomeSectorController::class);
    Route::resource('home-ads', HomeAdsController::class);
    Route::resource('home-documentation', HomeDocumentationController::class);
    Route::resource('home-partner', HomePartnerController::class);
    Route::resource('article', ArticleController::class);
    Route::patch('article/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('article.toggle-status');
    Route::get('/bpd', [BpdController::class, 'index'])->name('bpd.index');
    Route::post('/bpd/refresh', [BpdController::class, 'refresh'])->name('bpd.refresh');
    Route::patch('media-corner/{mediaCorner}/toggle-status', [MediaCornerController::class, 'toggleStatus'])->name('media-corner.toggle-status');
    Route::resource('media-corner', MediaCornerController::class, ['only' => ['index', 'destroy']]);
    Route::resource('benefit', BenefitController::class);
    Route::resource('aboutus-information', AboutUsInformationController::class, ['only' => ['index', 'store']]);
    Route::resource('aboutus-history', AboutUsHistoryController::class, ['only' => ['index', 'store']]);
    Route::resource('direction-commitment', DirectionCommitmentController::class);
    Route::resource('honorary-council', HonoraryCouncilController::class);
    Route::resource('regional-coordinator', RegionalCoordinatorController::class);
    Route::resource('organization', OrganizationController::class);

    // Excel Import/Export for Positions
    Route::get('organization/export/positions', [OrganizationController::class, 'exportPositions'])->name('organization.export.positions');
    Route::post('organization/import/positions', [OrganizationController::class, 'importPositions'])->name('organization.import.positions');
    Route::get('organization/template/positions', [OrganizationController::class, 'downloadPositionTemplate'])->name('organization.template.positions');

    Route::resource('member', MemberController::class, ['except' => ['show']]);

    // Excel Import/Export for Members
    Route::get('member/export/excel', [MemberController::class, 'exportMembers'])->name('member.export.excel');
    Route::post('member/import/excel', [MemberController::class, 'importMembers'])->name('member.import.excel');
    Route::get('member/template/excel', [MemberController::class, 'downloadMemberTemplate'])->name('member.template.excel');
    // add export excel dashboardexport
    // Route::get('dashboard-export', [DashboardV2Controller::class, 'export'])->name('dashboard.export');

    // // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');          // old dashboard
    // Route::get('dashboard', [DashboardV2Controller::class, 'index'])->name('dashboard.index');
});
Route::get('translate', [TranslateController::class, 'index'])->name('translate');
Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');
