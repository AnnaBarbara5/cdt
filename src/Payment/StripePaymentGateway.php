<?php

declare(strict_types=1);

namespace App\Payment;

/**
 * Integração opcional com Stripe (tokenização via PaymentMethod).
 * Requer STRIPE_SECRET_KEY e PAYMENT_GATEWAY=stripe no .env.
 */
final class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $secretKey,
    ) {
    }

    public function tokenize(array $paymentInput): array
    {
        $digits = PaymentSanitizer::digitsOnly($paymentInput['cartao']);

        $payload = http_build_query([
            'type' => 'card',
            'card' => [
                'number' => $digits,
                'exp_month' => substr($paymentInput['validade'], 0, 2),
                'exp_year' => '20' . substr($paymentInput['validade'], -2),
                'cvc' => $paymentInput['cvv'],
            ],
        ]);

        $ch = curl_init('https://api.stripe.com/v1/payment_methods');
        if ($ch === false) {
            throw new \RuntimeException('cURL indisponível para Stripe.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $status < 200 || $status >= 300) {
            return [
                'token' => hash('sha256', 'stripe-fallback-' . $digits),
                'status' => 'falha_tokenizacao',
            ];
        }

        /** @var array<string, mixed>|null $json */
        $json = json_decode($response, true);
        $id = is_array($json) ? (string) ($json['id'] ?? '') : '';

        return [
            'token' => $id !== '' ? $id : hash('sha256', 'stripe-' . $digits),
            'status' => 'tokenizado_stripe',
        ];
    }
}