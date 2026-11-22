<?php

declare(strict_types=1);

namespace App\Payment;

use App\Config;

final class PaymentGatewayFactory
{
    public static function create(): PaymentGatewayInterface
    {
        $driver = Config::getString('PAYMENT_GATEWAY', 'mock');
        $stripeKey = Config::getString('STRIPE_SECRET_KEY', '');

        if ($driver === 'stripe' && $stripeKey !== '' && function_exists('curl_init')) {
            return new StripePaymentGateway($stripeKey);
        }

        return new MockPaymentGateway();
    }
}