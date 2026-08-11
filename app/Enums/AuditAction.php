<?php

namespace App\Enums;

enum AuditAction: string
{
    case ClinicOnboarded = 'clinic_onboarded';
    case ClinicUpdated = 'clinic_updated';
    case BranchCreated = 'branch_created';
    case StaffCreated = 'staff_created';
    case StaffUpdated = 'staff_updated';
    case StaffDeactivated = 'staff_deactivated';
    case SubscriptionPaymentSubmitted = 'subscription_payment_submitted';
    case SubscriptionPaymentVerified = 'subscription_payment_verified';
    case PaymentSettingsUpdated = 'payment_settings_updated';
    case SubscriptionPlanCreated = 'subscription_plan_created';
    case SubscriptionPlanUpdated = 'subscription_plan_updated';
    case RegistrationRequestSubmitted = 'registration_request_submitted';
    case RegistrationRequestApproved = 'registration_request_approved';
    case RegistrationRequestRejected = 'registration_request_rejected';

    public function label(): string
    {
        return match ($this) {
            self::ClinicOnboarded => __('Clinic onboarded'),
            self::ClinicUpdated => __('Clinic updated'),
            self::BranchCreated => __('Branch created'),
            self::StaffCreated => __('Staff created'),
            self::StaffUpdated => __('Staff updated'),
            self::StaffDeactivated => __('Staff deactivated'),
            self::SubscriptionPaymentSubmitted => __('Payment submitted'),
            self::SubscriptionPaymentVerified => __('Payment verified'),
            self::PaymentSettingsUpdated => __('Payment settings updated'),
            self::SubscriptionPlanCreated => __('Subscription plan created'),
            self::SubscriptionPlanUpdated => __('Subscription plan updated'),
            self::RegistrationRequestSubmitted => __('Registration request submitted'),
            self::RegistrationRequestApproved => __('Registration request approved'),
            self::RegistrationRequestRejected => __('Registration request rejected'),
        };
    }

    public function shouldAlert(): bool
    {
        return in_array($this, [
            self::RegistrationRequestSubmitted,
            self::ClinicOnboarded,
            self::StaffDeactivated,
            self::SubscriptionPaymentSubmitted,
            self::SubscriptionPaymentVerified,
            self::PaymentSettingsUpdated,
            self::SubscriptionPlanCreated,
        ], true);
    }
}
