<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use App\Security\AdminAuth;
use PHPUnit\Framework\TestCase;

final class AdminAuthTest extends TestCase
{
    private ?string $previousAdminHash = null;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->previousAdminHash = $_ENV['ADMIN_PASSWORD_HASH'] ?? null;
        Config::resetForTests();
    }

    protected function tearDown(): void
    {
        if ($this->previousAdminHash !== null) {
            putenv('ADMIN_PASSWORD_HASH=' . $this->previousAdminHash);
            $_ENV['ADMIN_PASSWORD_HASH'] = $this->previousAdminHash;
        }
        Config::resetForTests();
    }

    public function testLoginLogoutFluxo(): void
    {
        $hash = password_hash('senha-secreta', PASSWORD_DEFAULT);
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, 'ADMIN_PASSWORD_HASH=' . $hash . "\n");
        Config::load($path);

        $auth = new AdminAuth();
        self::assertFalse($auth->isLoggedIn());
        self::assertTrue($auth->verifyPassword('senha-secreta'));
        $auth->login();
        self::assertTrue($auth->isLoggedIn());
        $auth->logout();
        self::assertFalse($auth->isLoggedIn());
    }
}