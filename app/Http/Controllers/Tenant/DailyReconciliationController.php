<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\DailyReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyReconciliationController extends Controller
{
    public function __construct(
        private readonly DailyReconciliationService $reconciliationService,
    ) {}

    public function __invoke(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $date = $request->filled('date')
            ? Carbon::parse($request->string('date')->toString())->startOfDay()
            : today();

        $report = $this->reconciliationService->forDate($date);

        if ($request->string('export')->toString() === 'csv') {
            return $this->exportCsv($report);
        }

        return view('tenant.reconciliation.show', [
            'report' => $report,
            'date' => $date,
        ]);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function exportCsv(array $report): StreamedResponse
    {
        $filename = 'daily-reconciliation-'.$report['date']->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', $report['date']->format('Y-m-d')]);
            fputcsv($handle, ['Total collected', number_format($report['total_collected'], 2, '.', '')]);
            fputcsv($handle, ['Payment count', $report['payment_count']]);
            fputcsv($handle, ['Discounts applied', number_format($report['discounts_applied'], 2, '.', '')]);
            fputcsv($handle, ['Outstanding balance', number_format($report['outstanding_balance'], 2, '.', '')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Method', 'Amount']);

            foreach ($report['collections_by_method'] as $method => $amount) {
                fputcsv($handle, [$method, number_format((float) $amount, 2, '.', '')]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Paid at', 'Patient', 'Invoice', 'Method', 'Amount', 'Reference']);

            foreach ($report['payments'] as $payment) {
                fputcsv($handle, [
                    $payment->paid_at?->format('Y-m-d H:i'),
                    $payment->invoice?->patient?->full_name,
                    $payment->invoice?->invoice_number,
                    $payment->payment_method?->value,
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->payment_reference,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
