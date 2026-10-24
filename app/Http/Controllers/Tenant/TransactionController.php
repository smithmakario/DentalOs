<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private ClinicAdminAccess $clinicAdminAccess,
        private ClinicOverviewService $overview,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $search = $request->string('search')->trim()->toString();
        $method = $request->string('payment_method')->trim()->toString();
        $branchId = $request->string('branch')->trim()->toString() ?: null;
        $canViewAllBranches = $this->clinicAdminAccess->canManageClinic();
        $organization = $this->clinicAdminAccess->organization();

        if ($canViewAllBranches && $organization !== null) {
            $transactions = $this->overview->collectFromBranches(
                $organization,
                $branchId,
                fn (Tenant $branch): Collection => $this->paymentsForBranch($branch, $search, $method),
            )->sortByDesc(fn (array $row): string => (string) ($row['paid_at'] ?? ''))->values();

            $branches = $this->overview->branches($organization);
        } else {
            $current = tenant();
            abort_unless($current instanceof Tenant, 404);

            $transactions = $this->paymentsForBranch($current, $search, $method);
            $branches = collect();
            $branchId = null;
        }

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $transactions->forPage($page, $perPage)->values(),
            $transactions->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('tenant.transactions.index', [
            'transactions' => $paginator,
            'search' => $search,
            'paymentMethod' => $method,
            'paymentMethods' => PaymentMethod::cases(),
            'canViewAllBranches' => $canViewAllBranches,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalAmount' => (float) $transactions->sum('amount'),
            'confirmedCount' => $transactions->where('is_confirmed', true)->count(),
            'withProofCount' => $transactions->where('has_proof', true)->count(),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function paymentsForBranch(Tenant $branch, string $search, string $method): Collection
    {
        return Payment::query()
            ->with(['invoice.patient'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('payment_number', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('invoice', function ($query) use ($search): void {
                            $query->where('invoice_number', 'like', "%{$search}%")
                                ->orWhereHas('patient', function ($query) use ($search): void {
                                    $query->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($method !== '', fn ($query) => $query->where('payment_method', $method))
            ->latest('paid_at')
            ->limit(200)
            ->get()
            ->map(fn (Payment $payment): array => $this->mapPayment($payment, $branch));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(Payment $payment, Tenant $branch): array
    {
        $invoice = $payment->invoice;

        return [
            'id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method?->label() ?? '—',
            'payment_reference' => $payment->payment_reference,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'paid_at_label' => $payment->paid_at?->format('M j, Y g:i A') ?? '—',
            'patient_name' => $invoice?->patient?->full_name ?? '—',
            'invoice_number' => $invoice?->invoice_number ?? '—',
            'invoice_url' => $invoice
                ? TenantPortal::url($branch, '/invoices/'.$invoice->id)
                : null,
            'branch_name' => $branch->name,
            'branch_id' => $branch->getTenantKey(),
            'has_proof' => $payment->hasProof(),
            'proof_url' => $payment->proofUrl(),
            'is_confirmed' => $payment->isConfirmed(),
            'confirmed_at_label' => $payment->confirmed_at?->format('M j, Y') ?? null,
        ];
    }
}
