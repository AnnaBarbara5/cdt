<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

final class JsonResponseTest extends TestCase
{
    public function testBuildSerializaJson(): void
    {
        $json = JsonResponse::build(200, ['sucesso' => true, 'mensagem' => 'ok']);
        self::assertJson($json);
        self::assertStringContainsString('ok', $json);
    }
}