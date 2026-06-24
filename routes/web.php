<?php

use App\Http\Controllers\LoginSecurityController;
use App\Livewire\Admin\JobsMonitoring;
use App\Livewire\Admin\LogsComponent;
use App\Livewire\Admin\PermissionComponent;
use App\Livewire\Admin\RolesComponent;
use App\Livewire\Admin\UsersComponent;
use App\Livewire\Admin\UserSessionComponent;
use App\Livewire\EmployeesComponent;
use App\Livewire\PasswordResetComponent;
use App\Livewire\ProfileComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('reset/password', PasswordResetComponent::class)->name('reset.password')->middleware('auth');

Route::group(['prefix'=>'2fa'], function(){
    Route::get('/',[LoginSecurityController::class, 'show2faForm'])->name('show2fa');
    Route::get('/disable-2fa',[LoginSecurityController::class, 'disable'])->name('disable-2fa');
            Route::post('/generateSecret',[LoginSecurityController::class, 'generate2faSecret'])->name('generate2faSecret');
            Route::post('/enable2fa',[LoginSecurityController::class, 'enable2fa'])->name('enable2fa');
            Route::post('/disable2fa',[LoginSecurityController::class, 'disable2fa'])->name('disable2fa');

    // 2fa middleware
    Route::post('/2faVerify', [LoginSecurityController::class, 'verify2fa'])->name('2faVerify')->middleware('2fa');
    Route::post('/confirm/trust-device', [LoginSecurityController::class, 'confirmTrustDevice'])->name('confirm.trust.device')->middleware('2fa');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
// Route::group(['middleware' => ['auth', '2fa', 'reset']], function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {



    });

});

// ─── Collector Surface ────────────────────────────────────────────────────────
Route::get('/collector/login', App\Livewire\Collector\LoginComponent::class)
    ->name('collector.login')
    ->middleware('guest');

Route::prefix('collector')->name('collector.')->middleware(['auth', 'role:collector'])->group(function () {
    Route::get('/route',              App\Livewire\Collector\TodayRouteComponent::class)->name('route');
    Route::get('/borrower/{borrowerId}', App\Livewire\Collector\BorrowerDetailComponent::class)->name('borrower');
    Route::get('/scan',               App\Livewire\Collector\QrScannerComponent::class)->name('scan');
    Route::get('/payment/{borrowerId}', App\Livewire\Collector\RecordPaymentComponent::class)->name('payment');
    Route::get('/payment/{paymentId}/confirmed', App\Livewire\Collector\PaymentConfirmationComponent::class)->name('payment.confirmed');
    Route::get('/summary',            App\Livewire\Collector\EndOfDaySummaryComponent::class)->name('summary');
    Route::get('/profile',            App\Livewire\Collector\ProfileComponent::class)->name('profile');

    // JSON API consumed by the PWA's offline cache + payment sync queue.
    Route::get('/api/route',     [App\Http\Controllers\Collector\RouteApiController::class, 'index'])->name('api.route');
    Route::post('/api/payments', [App\Http\Controllers\Collector\PaymentApiController::class, 'store'])->name('api.payments');
});

// Admin-facing one-time onboarding checklist for setting up a new collector's
// device as an installable PWA. Visible to office staff, not collectors.
Route::get('/collector-onboarding', App\Livewire\Collector\OnboardingComponent::class)
    ->name('collector.onboarding')
    ->middleware(['auth', 'role:admin|staff|collector']);

// ─── Admin Surface ────────────────────────────────────────────────────────────
Route::prefix('admin-panel')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/',                   App\Livewire\Admin\MainDashboardComponent::class)->name('dashboard');
    Route::get('/borrowers',          App\Livewire\Admin\BorrowersListComponent::class)->name('borrowers');
    Route::get('/borrowers/{borrowerId}', App\Livewire\Admin\BorrowerDetailComponent::class)->name('borrowers.show');
    Route::get('/loans/create',       App\Livewire\Admin\ReleaseNewLoanComponent::class)->name('loans.create');
    Route::get('/monitor',            App\Livewire\Admin\CollectionsMonitorComponent::class)->name('monitor');
    Route::get('/settings',           App\Livewire\Admin\SettingsComponent::class)->name('settings');
    Route::get('/loyalty',            App\Livewire\Admin\LoyaltyRebatesComponent::class)->name('loyalty');
    Route::get('/rebates',            App\Livewire\Admin\PendingRebatesComponent::class)->name('rebates');
});

// ─── Borrower Surface ─────────────────────────────────────────────────────────
Route::get('/borrower/login', App\Livewire\Borrower\LoginComponent::class)
    ->name('borrower.login')
    ->middleware('guest');

Route::prefix('borrower')->name('borrower.')->middleware(['auth', 'role:borrower'])->group(function () {
    Route::get('/home',     App\Livewire\Borrower\HomeComponent::class)->name('home');
    Route::get('/schedule', App\Livewire\Borrower\RepaymentScheduleComponent::class)->name('schedule');
    Route::get('/rewards',  App\Livewire\Borrower\RewardsComponent::class)->name('rewards');
    Route::get('/history',  App\Livewire\Borrower\LoanHistoryComponent::class)->name('history');
    Route::get('/profile',  App\Livewire\Borrower\ProfileComponent::class)->name('profile');
});