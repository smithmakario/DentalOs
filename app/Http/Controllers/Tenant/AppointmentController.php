<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAppointmentRequest;
use App\Http\Requests\Tenant\UpdateAppointmentRequest;
use App\Http\Requests\Tenant\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\BranchProfile;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\Staff;
use App\Services\AppointmentSchedulingService;
use App\Support\AppointmentWizard;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentSchedulingService $schedulingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $providerId = $request->integer('provider_id');
        $period = $request->string('period')->trim()->toString() ?: 'upcoming';

        $appointments = Appointment::query()
            ->with(['patient', 'provider'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('patient', function (Builder $query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($providerId > 0, fn (Builder $query) => $query->where('provider_id', $providerId))
            ->when($period === 'today', fn (Builder $query) => $query->whereDate('scheduled_at', today()))
            ->when($period === 'upcoming', fn (Builder $query) => $query->where('scheduled_at', '>=', now()))
            ->when($period === 'past', fn (Builder $query) => $query->where('scheduled_at', '<', now()))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('scheduled_at', $request->date('date')))
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('tenant.appointments.index', [
            'appointments' => $appointments,
            'providers' => Staff::providers()->orderBy('first_name')->get(),
            'search' => $search,
            'status' => $status,
            'providerId' => $providerId,
            'period' => $period,
            'date' => $request->input('date'),
            'todayCount' => Appointment::whereDate('scheduled_at', today())->count(),
            'upcomingCount' => Appointment::where('scheduled_at', '>=', now())->count(),
            'completedToday' => Appointment::whereDate('scheduled_at', today())
                ->where('status', AppointmentStatus::Completed)
                ->count(),
            'cancelledCount' => Appointment::where('status', AppointmentStatus::Cancelled)->count(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $wizard = AppointmentWizard::state($request);

        if ($wizard['step'] >= 2 && ! $wizard['service_id']) {
            return redirect()->to(AppointmentWizard::url($wizard, 1));
        }

        if ($wizard['step'] >= 3 && ! $wizard['provider_id']) {
            return redirect()->to(AppointmentWizard::url($wizard, 2));
        }

        if ($wizard['step'] >= 4 && (! $wizard['date'] || ! $wizard['time'])) {
            return redirect()->to(AppointmentWizard::url($wizard, 3));
        }

        $selectedService = $wizard['service_id']
            ? ClinicService::query()->where('is_active', true)->find($wizard['service_id'])
            : null;

        if ($wizard['step'] >= 2 && $selectedService === null) {
            return redirect()->to(AppointmentWizard::url($wizard, 1));
        }

        $selectedProvider = $wizard['provider_id']
            ? Staff::query()
                ->where('role', StaffRole::Dentist)
                ->where('is_active', true)
                ->find($wizard['provider_id'])
            : null;

        if ($wizard['step'] >= 3 && $selectedProvider === null) {
            return redirect()->to(AppointmentWizard::url($wizard, 2));
        }

        $services = ClinicService::query()
            ->where('is_active', true)
            ->when($wizard['search'], function (Builder $query) use ($wizard): void {
                $query->where(function (Builder $query) use ($wizard): void {
                    $query->where('name', 'like', "%{$wizard['search']}%")
                        ->orWhere('description', 'like', "%{$wizard['search']}%")
                        ->orWhere('category', 'like', "%{$wizard['search']}%");
                });
            })
            ->orderByDesc('is_recommended')
            ->orderBy('name')
            ->get();

        $dentists = $this->schedulingService->dentists();
        $branchProfile = BranchProfile::query()->first();
        $durationMinutes = $selectedService?->duration_minutes ?? 30;

        $calendarMonth = $this->schedulingService->parseMonth(
            $wizard['month'] ?? ($wizard['date'] ? Carbon::parse($wizard['date'])->format('Y-m') : now()->format('Y-m')),
        );

        $selectedDate = $wizard['date'] ? Carbon::parse($wizard['date']) : $calendarMonth->copy()->day(max(1, (int) now()->format('j')));

        if ($selectedDate->isPast() && ! $selectedDate->isToday()) {
            $selectedDate = now()->startOfDay();
        }

        $calendarDays = $selectedProvider
            ? $this->schedulingService->calendarDays($calendarMonth, $selectedProvider->id, $durationMinutes, $selectedDate)
            : [];

        $timeSlots = $selectedProvider
            ? $this->schedulingService->slotsForDay($selectedProvider->id, $selectedDate, $durationMinutes)
            : ['morning' => [], 'afternoon' => [], 'evening' => []];

        $dentistAvailability = $dentists->mapWithKeys(function (Staff $dentist) use ($durationMinutes): array {
            $next = $this->schedulingService->nextAvailableSlot($dentist->id, $durationMinutes);

            return [
                $dentist->id => $next,
            ];
        });

        return view('tenant.appointments.create', [
            'wizard' => $wizard,
            'services' => $services,
            'dentists' => $dentists,
            'selectedService' => $selectedService,
            'selectedProvider' => $selectedProvider,
            'branchProfile' => $branchProfile,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $calendarDays,
            'selectedDate' => $selectedDate,
            'timeSlots' => $timeSlots,
            'dentistAvailability' => $dentistAvailability,
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'appointment' => new Appointment([
                'status' => AppointmentStatus::Scheduled,
                'duration_minutes' => $durationMinutes,
                'patient_id' => $wizard['patient_id'],
                'provider_id' => $wizard['provider_id'],
            ]),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $appointment = Appointment::create([
            'patient_id' => $request->integer('patient_id'),
            'provider_id' => $request->integer('provider_id'),
            'title' => $request->string('title')->toString(),
            'scheduled_at' => $request->date('scheduled_at'),
            'duration_minutes' => $request->integer('duration_minutes'),
            'status' => $request->enum('status', AppointmentStatus::class),
            'notes' => $request->string('notes')->trim()->toString() ?: null,
        ]);

        return redirect()
            ->route('tenant.appointments.show', $appointment)
            ->with('success', __('Appointment scheduled successfully.'));
    }

    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'provider']);

        return view('tenant.appointments.show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        $appointment->load('patient');

        return view('tenant.appointments.edit', [
            'appointment' => $appointment,
            'patients' => Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(),
            'providers' => Staff::providers()->orderBy('first_name')->get(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return redirect()
            ->route('tenant.appointments.show', $appointment)
            ->with('success', __('Appointment updated successfully.'));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return redirect()
            ->route('tenant.appointments.index')
            ->with('success', __('Appointment archived successfully.'));
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('updateStatus', $appointment);

        $status = $request->enum('status', AppointmentStatus::class);

        $updates = ['status' => $status];

        if ($status === AppointmentStatus::CheckedIn && $appointment->checked_in_at === null) {
            $updates['checked_in_at'] = now();
        }

        if ($status === AppointmentStatus::Completed && $appointment->completed_at === null) {
            $updates['completed_at'] = now();
        }

        $appointment->update($updates);

        return redirect()
            ->route('tenant.appointments.show', $appointment)
            ->with('success', __('Appointment status updated.'));
    }
}
