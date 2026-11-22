<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use App\Config;
use App\Database\ConnectionFactory;
use App\Http\CadastroApiHandler;
use App\Http\PayloadSanitizer;
use App\Payment\PaymentGatewayFactory;
use App\Repository\ClienteRepository;
use App\Security\AdminAuth;
use App\Security\Csrf;
use App\Security\RateLimiter;
use App\Security\SessionManager;
use App\Service\CadastroService;
use App\Support\CpfMask;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

/**
 * Critérios de aceite do pré-projeto e segurança PCI aplicados ao domínio.
 */
final class AcceptanceCriteriaTest extends TestCase
{
    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SESSION = [];
        SessionManager::start();
    }

    public function testCa01CadastroCompletoPersistidoSemPanNemCvv(): void
    {
        $result = CadastroService::createDefault()->registrar(SamplePayload::valid());
        self::assertTrue($result['sucesso']);

        $repo = new ClienteRepository();
        $repo->ensureSchema();
        $rows = $repo->findAll();
        self::assertCount(1, $rows);
        self::assertSame('1111', $rows[0]['cartao_ultimos4']);
        self::assertArrayNotHasKey('numero_cartao', $rows[0]);
        self::assertArrayNotHasKey('cvv', $rows[0]);
        self::assertNotEmpty($rows[0]['pagamento_token']);
    }

    public function testCa02RejeitaPayloadInvalido(): void
    {
        $result = CadastroService::createDefault()->registrar(['email' => 'x']);
        self::assertFalse($result['sucesso']);
        self::assertNotEmpty($result['erros']);
    }

    public function testCa03ApiExigeCsrf(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = SamplePayload::valid();
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(403, $response['status']);
    }

    public function testCa04RespostaApiSemDepuracaoEmProducao(): void
    {
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, "APP_DEBUG=false\n");
        Config::resetForTests();
        Config::load($path);
        $sanitized = PayloadSanitizer::forClient([
            'sucesso' => false,
            'erros' => [
                ['codigo' => 'x', 'campo' => 'geral', 'mensagem' => 'Erro', 'depuracao' => 'interno'],
            ],
        ]);
        self::assertArrayNotHasKey('depuracao', $sanitized['erros'][0]);
    }

    public function testCa05PainelMascaraCpf(): void
    {
        self::assertSame('529.***.***-25', CpfMask::mask('52998224725'));
    }

    public function testCa06PaginacaoRepositorio(): void
    {
        $repo = new ClienteRepository();
        $repo->ensureSchema();
        $service = CadastroService::createDefault();
        for ($i = 0; $i < 3; $i++) {
            $payload = SamplePayload::valid();
            $payload['email'] = "user{$i}@example.com";
            $service->registrar($payload);
        }
        self::assertSame(3, $repo->countAll());
        self::assertCount(2, $repo->findPage(1, 2));
        self::assertCount(1, $repo->findPage(2, 2));
    }

    public function testCa07AutenticacaoAdminComHash(): void
    {
        $hash = password_hash('admin-test', PASSWORD_DEFAULT);
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, 'ADMIN_PASSWORD_HASH=' . $hash . "\n");
        Config::resetForTests();
        Config::load($path);
        $auth = new AdminAuth();
        self::assertTrue($auth->verifyPassword('admin-test'));
        self::assertFalse($auth->verifyPassword('senha-errada'));
    }

    public function testCa08RateLimitBloqueiaExcesso(): void
    {
        $limiter = new RateLimiter();
        for ($i = 0; $i < 5; $i++) {
            $limiter->allow('aceite-bucket', 5, 3600);
        }
        self::assertFalse($limiter->allow('aceite-bucket', 5, 3600));
    }

    public function testCa09GatewayMockPadrao(): void
    {
        putenv('PAYMENT_GATEWAY=mock');
        self::assertInstanceOf(\App\Payment\MockPaymentGateway::class, PaymentGatewayFactory::create());
    }

    public function testCa10ExclusaoRegistroLgpd(): void
    {
        $result = CadastroService::createDefault()->registrar(SamplePayload::valid());
        self::assertTrue($result['sucesso']);
        $repo = new ClienteRepository();
        $repo->ensureSchema();
        $id = (int) $repo->findAll()[0]['id'];
        self::assertTrue($repo->deleteById($id));
        self::assertSame(0, $repo->countAll());
    }

    public function testCa03bCadastroComCsrfRetornaSucesso(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = SamplePayload::valid();
        $_POST['csrf_token'] = Csrf::token();
        $response = CadastroApiHandler::createDefault()->execute();
        self::assertSame(200, $response['status']);
        self::assertTrue($response['payload']['sucesso']);
    }
}