<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Clinic\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Clinic\RedirectToAdminBranchController;
use Illuminate\Support\Facades\Route;

Route::prefix('clinic')->name('clinic.')->group(function (): void {
    Route::middleware('guest:staff')->group(function (): void {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
        Route::get('auth/google', [GoogleAuthController::class, 'redirectClinic'])->name('auth.google');
    });

    // Authenticated /clinic/* URLs send primary admins to their admin branch path.
    Route::middleware('clinic')->group(function (): void {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', RedirectToAdminBranchController::class)->defaults('path', 'dashboard');
        Route::get('dashboard', RedirectToAdminBranchController::class)->name('dashboard')->defaults('path', 'dashboard');
        Route::get('patients', RedirectToAdminBranchController::class)->name('patients.index')->defaults('path', 'patients');
        Route::get('appointments', RedirectToAdminBranchController::class)->name('appointments.index')->defaults('path', 'appointments');
        Route::get('treatments', RedirectToAdminBranchController::class)->name('treatments.index')->defaults('path', 'treatment-plans');
        Route::get('services', RedirectToAdminBranchController::class)->name('services.index')->defaults('path', 'clinic-services');
        Route::get('staff', RedirectToAdminBranchController::class)->name('staff.index')->defaults('path', 'staff');
        Route::get('branches', RedirectToAdminBranchController::class)->name('branches.index')->defaults('path', 'clinic-branches');
        Route::get('billing', RedirectToAdminBranchController::class)->name('billing.index')->defaults('path', 'transactions');
        Route::get('transactions', RedirectToAdminBranchController::class)->name('transactions.index')->defaults('path', 'transactions');
        Route::get('reconciliation', RedirectToAdminBranchController::class)->name('reconciliation')->defaults('path', 'reconciliation');
        Route::get('support', RedirectToAdminBranchController::class)->name('support')->defaults('path', 'dashboard');
    });
});
