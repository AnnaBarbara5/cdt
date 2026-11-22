<?php

declare(strict_types=1);

namespace App\Payment;

interface PaymentGatewayInterface
{
    /**
     * @param array{cartao: string, validade: string, cvv: string, pagamento: string, nome_cartao: string, cpf_cartao: string} $paymentInput
     * @return array{token: string, status: string}
     */
    public function tokenize(array $paymentInput): array;
}