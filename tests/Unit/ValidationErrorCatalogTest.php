<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\ValidationErrorCatalog;
use PHPUnit\Framework\TestCase;

final class ValidationErrorCatalogTest extends TestCase
{
    public function testCartaoInvalidoPossuiCodigoECampo(): void
    {
        $entry = ValidationErrorCatalog::entry('cartao_invalido');
        self::assertSame('cartao_invalido', $entry['codigo']);
        self::assertSame('cartao', $entry['campo']);
        self::assertStringContainsString('Luhn', $entry['depuracao']);
    }
}