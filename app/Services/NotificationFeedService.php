<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PlatformRole;
use App\Enums\RegistrationRequestStatus;
use App\Enums\SubscriptionPaymentStatus;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\OrganizationRegistrationRequest;
use App\Models\Staff;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationFeedService
{
    /**
     * @return array{items: Collection<int, array{title: string, body: string, url: string|null, time: string|null, unread: bool}>, unread_count: int}
     */
    public function forPlatformUser(User $user): array
    {
        $items = collect();

        if ($user->platform_role === PlatformRole::SuperAdmin) {
            OrganizationRegistrationRequest::query()
                ->where('status', RegistrationRequestStatus::Pending)
                ->latest()
                ->limit(5)
                ->get()
                ->each(function (OrganizationRegistrationRequest $request) use ($items): void {
                    $items->push([
                        'title' => __('New registration request'),
                        'body' => $request->name.' · '.($request->email ?? ''),
                        'url' => route('registration-requests.show', $request),
                        'time' => $request->created_at?->diffForHumans(),
                        'unread' => true,
                    ]);
                });

            SubscriptionPayment::query()
                ->with('organization')
                ->where('status', SubscriptionPaymentStatus::AwaitingVerification)
                ->latest()
                ->limit(5)
                ->get()
                ->each(function (SubscriptionPayment $payment) use ($items): void {
                    $items->push([
                        'title' => __('Payment awaiting verification'),
                        'body' => $payment->organization?->name ?? __('Unknown clinic'),
                        'url' => route('subscriptions.index'),
                        'time' => $payment->created_at?->diffForHumans(),
                        'unread' => true,
                    ]);
                });

            Organization::query()
                ->where('is_active', false)
                ->latest()
                ->limit(5)
                ->get()
                ->each(function (Organization $organization) use ($items): void {
                    $items->push([
                        'title' => __('Clinic awaiting activation'),
                        'body' => $organization->name,
                        'url' => route('clinics.show', $organization),
                        'time' => $organization->created_at?->diffForHumans(),
                        'unread' => true,
                    ]);
                });

            AuditLog::query()
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->each(function (AuditLog $log) use ($items): void {
                    $items->push([
                        'title' => $log->action->label(),
                        'body' => $log->description,
                        'url' => route('audit-logs.index'),
                        'time' => $log->created_at?->diffForHumans(),
                        'unread' => false,
                    ]);
                });
        } else {
            $user->organizations()
                ->with('adminBranch')
                ->get()
                ->each(function (Organization $organization) use ($items): void {
                    if (! $organization->is_active) {
                        $items->push([
                            'title' => __('Clinic not active yet'),
                            'body' => $organization->name.' — '.__('Waiting for subscription activation.'),
                            'url' => route('clinics.show', $organization),
                            'time' => $organization->updated_at?->diffForHumans(),
                            'unread' => true,
                        ]);

                        return;
                    }

                    $items->push([
                        'title' => __('Clinic ready'),
                        'body' => $organization->name.(
                            $organization->adminBranch
                                ? ' — '.__('Admin branch: :branch', ['branch' => $organization->adminBranch->name])
                                : ''
                        ),
                        'url' => route('clinics.show', $organization),
                        'time' => $organization->updated_at?->diffForHumans(),
                        'unread' => false,
                    ]);
                });
        }

        if ($items->isEmpty()) {
            $items->push([
                'title' => __('You\'re all caught up'),
                'body' => __('No new platform alerts right now.'),
                'url' => null,
                'time' => null,
                'unread' => false,
            ]);
        }

        $items = $items->take(10)->values();

        return [
            'items' => $items,
            'unread_count' => $items->where('unread', true)->count(),
        ];
    }

    /**
     * @return array{items: Collection<int, array{title: string, body: string, url: string|null, time: string|null, unread: bool}>, unread_count: int}
     */
    public function forStaff(Staff $staff): array
    {
        $items = collect();

        Appointment::query()
            ->with('patient')
            ->whereDate('scheduled_at', today())
            ->whereIn('status', [
                AppointmentStatus::Scheduled,
                AppointmentStatus::Confirmed,
                AppointmentStatus::CheckedIn,
                AppointmentStatus::InProgress,
            ])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->each(function (Appointment $appointment) use ($items): void {
                $items->push([
                    'title' => __('Today\'s appointment'),
                    'body' => trim(($appointment->patient?->full_name ?? __('Patient')).' · '.$appointment->scheduled_at?->format('g:i A')),
                    'url' => route('tenant.appointments.show', $appointment),
                    'time' => $appointment->scheduled_at?->diffForHumans(),
                    'unread' => true,
                ]);
            });

        Invoice::query()
            ->with('patient')
            ->unpaid()
            ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Partial])
            ->latest()
            ->limit(5)
            ->get()
            ->each(function (Invoice $invoice) use ($items): void {
                $items->push([
                    'title' => __('Outstanding invoice'),
                    'body' => trim(($invoice->patient?->full_name ?? __('Patient')).' · #'.$invoice->id),
                    'url' => route('tenant.invoices.show', $invoice),
                    'time' => $invoice->created_at?->diffForHumans(),
                    'unread' => true,
                ]);
            });

        if ($items->isEmpty()) {
            $items->push([
                'title' => __('You\'re all caught up'),
                'body' => __('No new clinic alerts right now.'),
                'url' => null,
                'time' => null,
                'unread' => false,
            ]);
        }

        $items = $items->take(10)->values();

        return [
            'items' => $items,
            'unread_count' => $items->where('unread', true)->count(),
        ];
    }
}
