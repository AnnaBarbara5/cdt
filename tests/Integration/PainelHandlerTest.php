<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Database\ConnectionFactory;
use App\Http\PainelHandler;
use App\Security\Csrf;
use App\Security\SessionManager;
use PHPUnit\Framework\TestCase;

final class PainelHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        SessionManager::start();
    }

    public function testRenderizaLoginQuandoNaoAutenticado(): void
    {
        ob_start();
        PainelHandler::createDefault()->handle();
        $html = ob_get_clean() ?: '';
        self::assertStringContainsString('Painel administrativo', $html);
        self::assertStringContainsString('name="csrf_token"', $html);
    }

    public function testLoginInvalidoMostraErro(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['senha'] = 'errada';
        $_POST['csrf_token'] = Csrf::token();
        ob_start();
        PainelHandler::createDefault()->handle();
        $html = ob_get_clean() ?: '';
        self::assertStringContainsString('Senha incorreta', $html);
    }
}