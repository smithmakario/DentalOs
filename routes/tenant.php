<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PatientAuthController;
use App\Http\Controllers\Api\PatientDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Tenant\AppointmentController;
use App\Http\Controllers\Tenant\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\Auth\NewPasswordController;
use App\Http\Controllers\Tenant\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\ClinicBranchController;
use App\Http\Controllers\Tenant\ClinicServiceController;
use App\Http\Controllers\Tenant\DailyReconciliationController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\InvoicePaymentController;
use App\Http\Controllers\Tenant\MediaController;
use App\Http\Controllers\Tenant\PatientAllergyController;
use App\Http\Controllers\Tenant\PatientController;
use App\Http\Controllers\Tenant\PatientDocumentController;
use App\Http\Controllers\Tenant\PatientVitalController;
use App\Http\Controllers\Tenant\PaymentConfirmationController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\TransactionController;
use App\Http\Controllers\Tenant\TreatmentPlanConsentController;
use App\Http\Controllers\Tenant\TreatmentPlanController;
use App\Http\Middleware\InitializeTenancyByOrganizationBranchPath;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$registerTenantWebRoutes = function (string $namePrefix): void {
    Route::get('/', function () {
        $tenant = tenant();

        if (auth('staff')->check()) {
            return $tenant instanceof Tenant
                ? redirect()->to(TenantPortal::absolute($tenant, '/dashboard'))
                : redirect()->route('tenant.dashboard');
        }

        return $tenant instanceof Tenant
            ? redirect()->to(TenantPortal::absolute($tenant, '/staff/login'))
            : redirect()->route('tenant.login');
    })->name($namePrefix.'home');

    // Public tenant files (logos, avatars) — path tenancy cannot use /tenancy/assets.
    Route::get('media/{path}', MediaController::class)
        ->where('path', '.*')
        ->name($namePrefix.'media');

    // Always show the shareable branch login page (even if a staff session exists).
    Route::get('staff/login', [AuthenticatedSessionController::class, 'create'])
        ->name($namePrefix.'login');

    Route::middleware('guest:staff')->group(function () use ($namePrefix): void {
        Route::post('staff/login', [AuthenticatedSessionController::class, 'store']);
        Route::get('staff/auth/google', [GoogleAuthController::class, 'redirectTenant'])
            ->name($namePrefix.'auth.google');

        Route::get('staff/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name($namePrefix.'password.request');

        Route::post('staff/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name($namePrefix.'password.email');

        Route::get('staff/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name($namePrefix.'password.reset');

        Route::post('staff/reset-password', [NewPasswordController::class, 'store'])
            ->name($namePrefix.'password.store');
    });

    Route::middleware(['staff', 'branch.staff'])->group(function () use ($namePrefix): void {
        Route::get('dashboard', DashboardController::class)
            ->name($namePrefix.'dashboard');

        Route::resource('staff', StaffController::class)->names($namePrefix.'staff');

        Route::resource('patients', PatientController::class)->names($namePrefix.'patients');
        Route::post('patients/{patient}/documents', [PatientDocumentController::class, 'store'])
            ->name($namePrefix.'patients.documents.store');
        Route::get('patients/{patient}/documents/{patient_document}/download', [PatientDocumentController::class, 'download'])
            ->name($namePrefix.'patients.documents.download');
        Route::delete('patients/{patient}/documents/{patient_document}', [PatientDocumentController::class, 'destroy'])
            ->name($namePrefix.'patients.documents.destroy');
        Route::post('patients/{patient}/allergies', [PatientAllergyController::class, 'store'])
            ->name($namePrefix.'patients.allergies.store');
        Route::delete('patients/{patient}/allergies/{allergy}', [PatientAllergyController::class, 'destroy'])
            ->name($namePrefix.'patients.allergies.destroy');
        Route::post('patients/{patient}/vitals', [PatientVitalController::class, 'store'])
            ->name($namePrefix.'patients.vitals.store');

        Route::resource('treatment-plans', TreatmentPlanController::class)->names($namePrefix.'treatment-plans');
        Route::post('treatment-plans/{treatment_plan}/options/{treatment_plan_option}/consent', [TreatmentPlanConsentController::class, 'store'])
            ->name($namePrefix.'treatment-plans.options.consent');
        Route::get('treatment-plans/{treatment_plan}/options/{treatment_plan_option}/consent-signature', [TreatmentPlanConsentController::class, 'signature'])
            ->name($namePrefix.'treatment-plans.options.consent-signature');

        Route::resource('clinic-services', ClinicServiceController::class)
            ->names($namePrefix.'clinic-services')
            ->except(['show']);

        Route::get('clinic-branches', [ClinicBranchController::class, 'index'])
            ->name($namePrefix.'clinic-branches.index');
        Route::post('clinic-branches', [ClinicBranchController::class, 'store'])
            ->name($namePrefix.'clinic-branches.store');

        Route::get('transactions', [TransactionController::class, 'index'])
            ->name($namePrefix.'transactions.index');

        Route::resource('invoices', InvoiceController::class)->names($namePrefix.'invoices');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
            ->name($namePrefix.'invoices.print');
        Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])
            ->name($namePrefix.'invoices.payments.store');
        Route::post('invoices/{invoice}/payments/{payment}/confirm', [PaymentConfirmationController::class, 'store'])
            ->name($namePrefix.'invoices.payments.confirm');
        Route::patch('invoices/{invoice}/void', [InvoiceController::class, 'void'])
            ->name($namePrefix.'invoices.void');
        Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])
            ->name($namePrefix.'invoices.status');
        Route::get('reconciliation', DailyReconciliationController::class)
            ->name($namePrefix.'reconciliation');

        Route::resource('appointments', AppointmentController::class)->names($namePrefix.'appointments');
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
            ->name($namePrefix.'appointments.status');
        Route::patch('appointments/{appointment}/clinical-notes', [AppointmentController::class, 'updateClinicalNotes'])
            ->name($namePrefix.'appointments.clinical-notes');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name($namePrefix.'logout');
    });
};

$registerTenantApiRoutes = function (): void {
    Route::post('/login', [PatientAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [PatientDashboardController::class, 'index']);
    });
};

// Domain-based access (canonical named routes for URL generation).
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () use ($registerTenantWebRoutes): void {
    $registerTenantWebRoutes('tenant.');
});

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () use ($registerTenantApiRoutes): void {
    $registerTenantApiRoutes();
});

// Path-based access on the central host: /{clinic-slug}/{branch-slug}/...
foreach (config('tenancy.central_domains') as $centralDomain) {
    Route::domain($centralDomain)
        ->prefix('{organization}/{branch}')
        ->middleware([
            // Initialize tenancy before the web stack (SubstituteBindings) so
            // route-model binding resolves tenant models on the branch database.
            InitializeTenancyByOrganizationBranchPath::class,
            'web',
        ])
        ->group(function () use ($registerTenantWebRoutes): void {
            $registerTenantWebRoutes('path.tenant.');
        });

    Route::domain($centralDomain)
        ->prefix('{organization}/{branch}/api')
        ->middleware([
            InitializeTenancyByOrganizationBranchPath::class,
            'api',
        ])
        ->group(function () use ($registerTenantApiRoutes): void {
            $registerTenantApiRoutes();
        });
}
