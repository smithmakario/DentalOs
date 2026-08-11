<?php

namespace App\Http\Controllers\Central;

use App\Enums\SubscriptionPaymentStatus;
use App\Http\Controllers\Controller;
use App\Services\SubscriptionPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PaystackService;

class PaystackController extends Controller
{
    public function __construct(
        private SubscriptionPaymentService $subscriptionPaymentService,
        private PaystackService $paystackService,
    ) {}

    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->string('reference')->toString();

        if ($reference === '') {
            return redirect()
                ->route('subscriptions.index')
                ->withErrors(['payment' => __('Payment reference missing.')]);
        }

        $payment = $this->subscriptionPaymentService->completePaystackPayment($reference);

        if ($payment === null) {
            return redirect()
                ->route('subscriptions.index')
                ->withErrors(['payment' => __('Payment record not found.')]);
        }

        if ($payment->status === SubscriptionPaymentStatus::Completed) {
            return redirect()
                ->route('subscriptions.index')
                ->with('status', __('Payment successful. Subscription activated.'));
        }

        return redirect()
            ->route('subscriptions.index')
            ->withErrors(['payment' => __('Payment verification failed.')]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $this->paystackService->validateWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->json('event');
        $reference = $request->json('data.reference');

        if ($event === 'charge.success' && is_string($reference)) {
            $this->subscriptionPaymentService->completePaystackPayment($reference);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
