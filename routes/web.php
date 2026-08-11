<?php

use App\Http\Controllers\Central\AnalyticsController;
use App\Http\Controllers\Central\AuditLogController;
use App\Http\Controllers\Central\BranchController;
use App\Http\Controllers\Central\ClinicController;
use App\Http\Controllers\Central\DashboardController as CentralDashboardController;
use App\Http\Controllers\Central\RegistrationRequestController;
use App\Http\Controllers\Central\PlatformPaymentSettingController;
use App\Http\Controllers\Central\StaffMemberController;
use App\Http\Controllers\Central\SubscriptionController;
use App\Http\Controllers\Central\SubscriptionPlanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->middleware('central')->group(function () {
        Route::get('/', function () {
            return auth()->check()
                ? redirect()->route('dashboard')
                : view('welcome');
        });

        Route::get('/get-started', [\App\Http\Controllers\GetStartedController::class, 'create'])->name('get-started.create');
        Route::post('/get-started', [\App\Http\Controllers\GetStartedController::class, 'store'])->name('get-started.store');
        Route::get('/get-started/thank-you', [\App\Http\Controllers\GetStartedController::class, 'thankYou'])->name('get-started.thank-you');
        Route::get('/get-started/onboarding/{token}', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('/get-started/onboarding/{token}', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');

        Route::get('/dashboard', CentralDashboardController::class)
            ->middleware(['auth', 'platform'])
            ->name('dashboard');

        Route::middleware(['auth', 'platform'])->group(function () {
            Route::get('/registration-requests', [RegistrationRequestController::class, 'index'])->name('registration-requests.index');
            Route::get('/registration-requests/{registrationRequest}', [RegistrationRequestController::class, 'show'])->name('registration-requests.show');
            Route::post('/registration-requests/{registrationRequest}/approve', [RegistrationRequestController::class, 'approve'])->name('registration-requests.approve');
            Route::post('/registration-requests/{registrationRequest}/reject', [RegistrationRequestController::class, 'reject'])->name('registration-requests.reject');
            Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
            Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
            Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');
            Route::get('/clinics/{organization}/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::patch('/clinics/{organization}', [ClinicController::class, 'update'])->name('clinics.update');
            Route::get('/clinics/{organization}/branches', [BranchController::class, 'index'])->name('clinics.branches.index');
            Route::post('/clinics/{organization}/branches', [BranchController::class, 'store'])->name('clinics.branches.store');
            Route::get('/clinics/{organization}/staff', [StaffMemberController::class, 'organizationIndex'])->name('clinics.staff.index');
            Route::get('/clinics/{organization}/staff/create', [StaffMemberController::class, 'create'])->name('clinics.staff.create');
            Route::post('/clinics/{organization}/staff', [StaffMemberController::class, 'store'])->name('clinics.staff.store');
            Route::get('/clinics/{organization}/staff/{staffMember}/edit', [StaffMemberController::class, 'edit'])->name('clinics.staff.edit');
            Route::patch('/clinics/{organization}/staff/{staffMember}', [StaffMemberController::class, 'update'])->name('clinics.staff.update');
            Route::delete('/clinics/{organization}/staff/{staffMember}', [StaffMemberController::class, 'destroy'])->name('clinics.staff.destroy');
            Route::get('/staff', [StaffMemberController::class, 'index'])->name('staff.index');
            Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
            Route::get('/subscription-plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
            Route::post('/subscription-plans', [SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
            Route::get('/subscription-plans/{subscriptionPlan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
            Route::patch('/subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
            Route::get('/subscriptions/settings', [PlatformPaymentSettingController::class, 'edit'])->name('subscriptions.settings.edit');
            Route::patch('/subscriptions/settings', [PlatformPaymentSettingController::class, 'update'])->name('subscriptions.settings.update');
            Route::get('/subscriptions/organizations/{organization}/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
            Route::post('/subscriptions/organizations/{organization}/checkout', [SubscriptionController::class, 'initiate'])->name('subscriptions.checkout.initiate');
            Route::get('/subscriptions/organizations/{organization}/manual', [SubscriptionController::class, 'manualForm'])->name('subscriptions.manual.create');
            Route::post('/subscriptions/organizations/{organization}/manual', [SubscriptionController::class, 'submitManual'])->name('subscriptions.manual.store');
            Route::post('/subscriptions/payments/{payment}/verify', [SubscriptionController::class, 'verifyManual'])->name('subscriptions.payments.verify');
            Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        Route::get('/paystack/callback', [PaystackController::class, 'callback'])
            ->middleware(['auth', 'platform'])
            ->name('paystack.callback');

        Route::post('/paystack/webhook', [PaystackController::class, 'webhook'])
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('paystack.webhook');

        require __DIR__.'/auth.php';
    });
}
