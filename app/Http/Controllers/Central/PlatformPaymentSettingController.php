<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\UpdatePlatformPaymentSettingRequest;
use App\Models\PlatformPaymentSetting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformPaymentSettingController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function edit(): View
    {
        $this->authorizeSuperAdmin();

        return view('central.subscriptions.settings', [
            'settings' => PlatformPaymentSetting::currentOrNew(),
            'paystackConfigured' => filled(config('paystack.secret_key')) && filled(config('paystack.public_key')),
        ]);
    }

    public function update(UpdatePlatformPaymentSettingRequest $request): RedirectResponse
    {
        $settings = PlatformPaymentSetting::currentOrNew();
        $settings->fill($request->validated());
        $settings->save();

        $this->auditLogService->record(
            AuditAction::PaymentSettingsUpdated,
            __('Updated platform bank payment settings.'),
            $settings,
            properties: ['bank_name' => $settings->bank_name],
        );

        return redirect()
            ->route('subscriptions.settings.edit')
            ->with('status', __('Bank details saved successfully.'));
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
