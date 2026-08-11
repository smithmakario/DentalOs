<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    /**
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initializeTransaction(
        string $email,
        int $amountInKobo,
        string $reference,
        string $callbackUrl,
        array $metadata = [],
    ): array {
        $response = $this->client()
            ->post('/transaction/initialize', [
                'email' => $email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'currency' => config('paystack.currency', 'NGN'),
                'metadata' => $metadata,
            ])
            ->throw()
            ->json('data');

        return [
            'authorization_url' => $response['authorization_url'],
            'access_code' => $response['access_code'],
            'reference' => $response['reference'],
        ];
    }

    /**
     * @return array{status: string, amount: int, currency: string, paid_at: ?string}
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->client()
            ->get('/transaction/verify/'.$reference)
            ->throw()
            ->json('data');

        return [
            'status' => $response['status'],
            'amount' => (int) $response['amount'],
            'currency' => $response['currency'],
            'paid_at' => $response['paid_at'] ?? null,
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('paystack.secret_key'));
    }

    public function validateWebhookSignature(string $payload, ?string $signature): bool
    {
        if ($signature === null || ! $this->isConfigured()) {
            return false;
        }

        $computed = hash_hmac('sha512', $payload, (string) config('paystack.secret_key'));

        return hash_equals($computed, $signature);
    }

    public function amountToKobo(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function client()
    {
        return Http::baseUrl(rtrim((string) config('paystack.base_url'), '/'))
            ->withToken((string) config('paystack.secret_key'))
            ->acceptJson()
            ->timeout(30)
            ->connectTimeout(10);
    }
}
