<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

final class ValidatorTest extends TestCase
{
    public function testPayloadValidoSemErros(): void
    {
        self::assertSame([], Validator::validateCadastroPayload(SamplePayload::valid()));
    }

    public function testPayloadInvalidoRetornaErros(): void
    {
        $errors = Validator::validateCadastroPayload([]);
        self::assertNotEmpty($errors);
    }

    #[DataProvider('cpfProvider')]
    public function testValidateCpf(string $cpf, bool $expected): void
    {
        self::assertSame($expected, Validator::validateCpf($cpf));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function cpfProvider(): array
    {
        return [
            'valido' => ['529.982.247-25', true],
            'repetido' => ['111.111.111-11', false],
            'curto' => ['123', false],
        ];
    }

    public function testValidacaoCartaoLuhn(): void
    {
        self::assertTrue(Validator::validateCardNumber('4111111111111111'));
        self::assertFalse(Validator::validateCardNumber('4111111111111112'));
    }

    public function testValidadeFutura(): void
    {
        self::assertTrue(Validator::validateCardExpiry('12/30'));
        self::assertFalse(Validator::validateCardExpiry('01/20'));
    }
}