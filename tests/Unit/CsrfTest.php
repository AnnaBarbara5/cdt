<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Security\Csrf;
use App\Security\SessionManager;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        SessionManager::start();
    }

    public function testGeraTokenEstavelNaSessao(): void
    {
        $a = Csrf::token();
        $b = Csrf::token();
        self::assertSame($a, $b);
        self::assertSame(64, strlen($a));
    }

    public function testValidaTokenCorreto(): void
    {
        $token = Csrf::token();
        self::assertTrue(Csrf::validate($token));
    }

    public function testRejeitaTokenIncorreto(): void
    {
        Csrf::token();
        self::assertFalse(Csrf::validate('invalido'));
    }
}