<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use App\Database\ConnectionFactory;
use App\Repository\ClienteRepository;
use App\Service\CadastroService;
use App\Service\DataRetentionService;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

final class DataRetentionServiceTest extends TestCase
{
    private string $dataDir;

    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        $this->dataDir = sys_get_temp_dir() . '/cdt-ret-' . uniqid('', true);
        mkdir($this->dataDir);

        $envFile = $this->dataDir . '/.env';
        file_put_contents($envFile, "LGPD_RETENTION_DAYS=30\nAPP_DEBUG=false\n");
        Config::resetForTests();
        Config::load($envFile);
    }

    public function testPurgeNowRemoveRegistrosAntigos(): void
    {
        $pdo = ConnectionFactory::get();
        $repo = new ClienteRepository();
        $repo->ensureSchema();

        CadastroService::createDefault()->registrar(SamplePayload::valid());
        $pdo->exec("UPDATE clientes_cartao SET criado_em = datetime('now', '-60 days')");

        $service = new DataRetentionService($repo, $this->dataDir);
        self::assertSame(1, $service->purgeNow());
        self::assertSame(0, $repo->countAll());
    }

    public function testPurgeIfDueRespeitaThrottle(): void
    {
        $repo = new ClienteRepository();
        $service = new DataRetentionService($repo, $this->dataDir);

        file_put_contents($this->dataDir . '/retention.last', (string) time());

        self::assertSame(0, $service->purgeIfDue());
    }
}