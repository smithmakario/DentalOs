<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyReconciliationService
{
    /**
     * @return array{
     *     date: CarbonInterface,
     *     collections_by_method: Collection<string, float>,
     *     total_collected: float,
     *     payment_count: int,
     *     discounts_applied: float,
     *     outstanding_balance: float,
     *     unpaid_invoice_count: int,
     *     voided_total: float,
     *     payments: Collection<int, Payment>
     * }
     */
    public function forDate(CarbonInterface $date): array
    {
        $payments = Payment::query()
            ->with(['invoice.patient'])
            ->whereDate('paid_at', $date)
            ->orderBy('paid_at')
            ->get();

        $collectionsByMethod = collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method): array => [
                $method->value => (float) $payments
                    ->where('payment_method', $method)
                    ->sum('amount'),
            ]);

        $discountsApplied = (float) Invoice::query()
            ->whereDate('issued_at', $date)
            ->where('status', '!=', InvoiceStatus::Void)
            ->sum('discount');

        $outstanding = Invoice::query()
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as outstanding')
            ->value('outstanding');

        $voidedTotal = (float) Invoice::query()
            ->where('status', InvoiceStatus::Void)
            ->whereDate('updated_at', $date)
            ->sum('total');

        return [
            'date' => $date->copy()->startOfDay(),
            'collections_by_method' => $collectionsByMethod,
            'total_collected' => (float) $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'discounts_applied' => $discountsApplied,
            'outstanding_balance' => (float) $outstanding,
            'unpaid_invoice_count' => Invoice::query()
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void])
                ->count(),
            'voided_total' => $voidedTotal,
            'payments' => $payments,
        ];
    }
}
