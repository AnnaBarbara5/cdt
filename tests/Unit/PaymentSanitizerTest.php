<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Payment\PaymentSanitizer;
use PHPUnit\Framework\TestCase;

final class PaymentSanitizerTest extends TestCase
{
    public function testUltimosQuatroDigitos(): void
    {
        self::assertSame('1111', PaymentSanitizer::lastFourDigits('4111-1111-1111-1111'));
    }

    public function testMascaraPan(): void
    {
        self::assertSame('**** **** **** 1111', PaymentSanitizer::maskPan('4111111111111111'));
    }

    public function testDetectBrand(): void
    {
        self::assertSame('visa', PaymentSanitizer::detectBrand('4111111111111111'));
        self::assertSame('mastercard', PaymentSanitizer::detectBrand('5555555555554444'));
        self::assertSame('amex', PaymentSanitizer::detectBrand('378282246310005'));
        self::assertSame('discover', PaymentSanitizer::detectBrand('6011111111111117'));
        self::assertSame('desconhecida', PaymentSanitizer::detectBrand('9999999999999999'));
    }

    public function testDigitsOnlyVazio(): void
    {
        self::assertSame('', PaymentSanitizer::digitsOnly(''));
        self::assertSame('', PaymentSanitizer::lastFourDigits('12'));
    }
}