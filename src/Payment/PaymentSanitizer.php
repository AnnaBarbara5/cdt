<?php

declare(strict_types=1);

namespace App\Payment;

final class PaymentSanitizer
{
    public static function digitsOnly(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        return $digits ?? '';
    }

    public static function lastFourDigits(string $cardNumber): string
    {
        $digits = self::digitsOnly($cardNumber);

        return strlen($digits) >= 4 ? substr($digits, -4) : '';
    }

    public static function detectBrand(string $cardNumber): string
    {
        $digits = self::digitsOnly($cardNumber);
        if (str_starts_with($digits, '4')) {
            return 'visa';
        }
        if (preg_match('/^5[1-5]/', $digits) === 1) {
            return 'mastercard';
        }
        if (preg_match('/^3[47]/', $digits) === 1) {
            return 'amex';
        }
        if (preg_match('/^6(?:011|5)/', $digits) === 1) {
            return 'discover';
        }

        return 'desconhecida';
    }

    public static function maskPan(string $cardNumber): string
    {
        $last4 = self::lastFourDigits($cardNumber);

        return $last4 !== '' ? '**** **** **** ' . $last4 : '****';
    }
}