<?php

namespace App\Support;

use Illuminate\Http\Request;

class AppointmentWizard
{
    /**
     * @return array{
     *     step: int,
     *     patient_id: int|null,
     *     service_id: int|null,
     *     provider_id: int|null,
     *     date: string|null,
     *     time: string|null,
     *     month: string|null,
     *     search: string|null,
     * }
     */
    public static function state(Request $request): array
    {
        $step = max(1, min(4, $request->integer('step') ?: 1));

        return [
            'step' => $step,
            'patient_id' => $request->integer('patient_id') ?: null,
            'service_id' => $request->integer('service_id') ?: null,
            'provider_id' => $request->integer('provider_id') ?: null,
            'date' => $request->string('date')->trim()->toString() ?: null,
            'time' => $request->string('time')->trim()->toString() ?: null,
            'month' => $request->string('month')->trim()->toString() ?: null,
            'search' => $request->string('search')->trim()->toString() ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $overrides
     */
    public static function url(array $state, int $step, array $overrides = []): string
    {
        return route('tenant.appointments.create', array_filter([
            'step' => $step,
            'patient_id' => $overrides['patient_id'] ?? $state['patient_id'],
            'service_id' => $overrides['service_id'] ?? $state['service_id'],
            'provider_id' => $overrides['provider_id'] ?? $state['provider_id'],
            'date' => $overrides['date'] ?? $state['date'],
            'time' => $overrides['time'] ?? $state['time'],
            'month' => $overrides['month'] ?? $state['month'] ?? null,
            'search' => $overrides['search'] ?? $state['search'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));
    }
}
