<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorExtendedTest extends TestCase
{
    public function testHelpersIndividuais(): void
    {
        self::assertTrue(Validator::requiredString('abc'));
        self::assertFalse(Validator::requiredString('   '));
        self::assertFalse(Validator::validateEmail(null));
        self::assertFalse(Validator::validateCpf(null));
        self::assertFalse(Validator::validateMinimumAge('invalido', 18));
        self::assertFalse(Validator::validateCep('123'));
        self::assertFalse(Validator::validatePaymentType('pix'));
        self::assertFalse(Validator::validateCardNumber(null));
        self::assertFalse(Validator::validateCardExpiry('99/99'));
        self::assertFalse(Validator::validateCvv('12'));
    }
}