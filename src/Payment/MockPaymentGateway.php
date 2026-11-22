<?php

declare(strict_types=1);

namespace App\Payment;

/**
 * Simula um PSP: recebe dados sensíveis em memória e devolve apenas um token opaco.
 * Nunca persiste PAN completo nem CVV.
 */
final class MockPaymentGateway implements PaymentGatewayInterface
{
    public function tokenize(array $paymentInput): array
    {
        $fingerprint = implode('|', [
            PaymentSanitizer::digitsOnly($paymentInput['cartao']),
            $paymentInput['validade'],
            $paymentInput['pagamento'],
            $paymentInput['nome_cartao'],
            preg_replace('/\D/', '', $paymentInput['cpf_cartao']) ?? '',
        ]);

        return [
            'token' => hash('sha256', 'cdt-mock-' . $fingerprint . '-' . bin2hex(random_bytes(8))),
            'status' => 'em_analise',
        ];
    }
}