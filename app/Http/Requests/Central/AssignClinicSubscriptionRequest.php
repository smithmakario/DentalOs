<?php

namespace App\Http\Requests\Central;

use App\Enums\BillingCycle;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignClinicSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $organization !== null && $this->user()->can('update', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')->where(function ($query) use ($organization): void {
                    $query->where('is_active', true);

                    if ($organization !== null) {
                        $query->where('organization_type', $organization->type->value);
                    }
                }),
            ],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subscription_plan_id' => __('subscription plan'),
            'billing_cycle' => __('billing cycle'),
        ];
    }

    public function plan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->findOrFail($this->validated('subscription_plan_id'));
    }
}
