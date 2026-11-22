<?php

declare(strict_types=1);

namespace App\Support;

final class CpfMask
{
    public static function mask(?string $cpf): string
    {
        if ($cpf === null || trim($cpf) === '') {
            return '';
        }

        $digits = preg_replace('/\D/', '', $cpf);
        if ($digits === null || strlen($digits) !== 11) {
            return '***';
        }

        return substr($digits, 0, 3) . '.***.***-' . substr($digits, -2);
    }
}