<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AppointmentController;
use App\Http\Controllers\Tenant\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\Auth\NewPasswordController;
use App\Http\Controllers\Tenant\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\ClinicServiceController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\InvoicePaymentController;
use App\Http\Controllers\Tenant\PatientController;
use App\Http\Controllers\Tenant\PatientDocumentController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\TreatmentPlanConsentController;
use App\Http\Controllers\Tenant\TreatmentPlanController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return auth('staff')->check()
            ? redirect()->route('tenant.dashboard')
            : redirect()->route('tenant.login');
    })->name('tenant.home');

    Route::middleware('guest:staff')->group(function () {
        Route::get('staff/login', [AuthenticatedSessionController::class, 'create'])
            ->name('tenant.login');

        Route::post('staff/login', [AuthenticatedSessionController::class, 'store']);

        Route::get('staff/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('tenant.password.request');

        Route::post('staff/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('tenant.password.email');

        Route::get('staff/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('tenant.password.reset');

        Route::post('staff/reset-password', [NewPasswordController::class, 'store'])
            ->name('tenant.password.store');
    });

    Route::middleware(['staff', 'branch.staff'])->group(function () {
        Route::get('dashboard', DashboardController::class)
            ->name('tenant.dashboard');

        Route::resource('staff', StaffController::class)->names('tenant.staff');

        Route::resource('patients', PatientController::class)->names('tenant.patients');
        Route::post('patients/{patient}/documents', [PatientDocumentController::class, 'store'])->name('tenant.patients.documents.store');
        Route::get('patients/{patient}/documents/{patient_document}/download', [PatientDocumentController::class, 'download'])->name('tenant.patients.documents.download');
        Route::delete('patients/{patient}/documents/{patient_document}', [PatientDocumentController::class, 'destroy'])->name('tenant.patients.documents.destroy');

        Route::resource('treatment-plans', TreatmentPlanController::class)->names('tenant.treatment-plans');
        Route::post('treatment-plans/{treatment_plan}/options/{treatment_plan_option}/consent', [TreatmentPlanConsentController::class, 'store'])
            ->name('tenant.treatment-plans.options.consent');
        Route::get('treatment-plans/{treatment_plan}/options/{treatment_plan_option}/consent-signature', [TreatmentPlanConsentController::class, 'signature'])
            ->name('tenant.treatment-plans.options.consent-signature');

        Route::resource('clinic-services', ClinicServiceController::class)->names('tenant.clinic-services')->except(['show']);

        Route::resource('invoices', InvoiceController::class)->names('tenant.invoices');
        Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])->name('tenant.invoices.payments.store');
        Route::patch('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('tenant.invoices.void');

        Route::resource('appointments', AppointmentController::class)->names('tenant.appointments');
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
            ->name('tenant.appointments.status');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('tenant.logout');
    });
});
