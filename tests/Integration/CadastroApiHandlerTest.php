<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Database\ConnectionFactory;
use App\Http\CadastroApiHandler;
use App\Security\Csrf;
use App\Security\SessionManager;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

final class CadastroApiHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = [];
        SessionManager::start();
        $_POST = SamplePayload::valid();
        $_POST['csrf_token'] = Csrf::token();
    }

    public function testPostRetornaSucesso(): void
    {
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(200, $response['status']);
        self::assertTrue($response['payload']['sucesso']);
    }

    public function testMetodoInvalido(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(405, $response['status']);
    }

    public function testOptionsRetorna204(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(204, $response['status']);
    }

    public function testRejeitaSemCsrf(): void
    {
        unset($_POST['csrf_token']);
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(403, $response['status']);
        self::assertFalse($response['payload']['sucesso']);
    }
}