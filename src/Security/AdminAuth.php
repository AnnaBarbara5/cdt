<?php

declare(strict_types=1);

namespace App\Security;

use App\Config;

final class AdminAuth
{
    public function verifyPassword(string $plainPassword): bool
    {
        $hash = Config::getString('ADMIN_PASSWORD_HASH', '');
        if ($hash === '') {
            return false;
        }

        return password_verify($plainPassword, $hash);
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['cdt_admin']) && $_SESSION['cdt_admin'] === true;
    }

    public function login(): void
    {
        $_SESSION['cdt_admin'] = true;
    }

    public function logout(): void
    {
        unset($_SESSION['cdt_admin']);
    }
}