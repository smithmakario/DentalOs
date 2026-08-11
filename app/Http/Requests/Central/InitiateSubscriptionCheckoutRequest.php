<?php

namespace App\Http\Requests\Central;

use App\Enums\BillingCycle;
use App\Enums\PlatformPaymentMethod;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InitiateSubscriptionCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $organization instanceof Organization
            && $this->user()?->can('update', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'payment_method' => ['required', Rule::enum(PlatformPaymentMethod::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Organization $organization */
                $organization = $this->route('organization');
                $plan = SubscriptionPlan::query()->find($this->integer('subscription_plan_id'));

                if ($plan === null || $plan->organization_type !== $organization->type) {
                    $validator->errors()->add(
                        'subscription_plan_id',
                        __('The selected plan is not available for this organization type.'),
                    );
                }
            },
        ];
    }
}
