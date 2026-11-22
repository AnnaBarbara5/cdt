<?php

declare(strict_types=1);

namespace App;

use App\Validation\ValidationErrorCatalog;

final class Validator
{
    /** @return list<array{codigo: string, campo: string, mensagem: string, depuracao: string}> */
    public static function validateCadastroPayload(array $data): array
    {
        $errors = [];

        if (!self::requiredString($data['nome'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('nome_obrigatorio');
        }

        if (!self::validateEmail($data['email'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('email_invalido');
        }

        if (!self::requiredString($data['telefone'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('telefone_obrigatorio');
        }

        if (!self::validateCpf($data['cpf_pessoal'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('cpf_pessoal_invalido');
        }

        if (!self::validateMinimumAge($data['data_nascimento'] ?? null, 18)) {
            $errors[] = ValidationErrorCatalog::entry('idade_minima');
        }

        if (!self::validateCep($data['cep'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('cep_invalido');
        }

        if (!self::requiredString($data['logradouro'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('logradouro_obrigatorio');
        }

        if (!self::requiredString($data['numero'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('numero_obrigatorio');
        }

        if (!self::validatePaymentType($data['pagamento'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('pagamento_invalido');
        }

        if (!self::validateCardNumber($data['cartao'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('cartao_invalido');
        }

        if (!self::validateCardExpiry($data['validade'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('validade_invalida');
        }

        if (!self::validateCvv($data['cvv'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('cvv_invalido');
        }

        if (!self::requiredString($data['nome_cartao'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('nome_cartao_obrigatorio');
        }

        if (!self::validateCpf($data['cpf_cartao'] ?? null)) {
            $errors[] = ValidationErrorCatalog::entry('cpf_cartao_invalido');
        }

        return $errors;
    }

    public static function requiredString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    public static function validateEmail(mixed $email): bool
    {
        if (!is_string($email) || trim($email) === '') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateCpf(mixed $cpf): bool
    {
        if (!is_string($cpf)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $cpf);
        if ($digits === null || strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }
            $rest = ($sum * 10) % 11;
            if ($rest === 10 || $rest === 11) {
                $rest = 0;
            }
            if ($rest !== (int) $digits[$t]) {
                return false;
            }
        }

        return true;
    }

    public static function validateMinimumAge(mixed $date, int $minimumYears): bool
    {
        if (!is_string($date) || $date === '') {
            return false;
        }

        $birth = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($birth === false) {
            return false;
        }

        $today = new \DateTimeImmutable('today');
        $age = (int) $birth->diff($today)->y;

        return $age >= $minimumYears;
    }

    public static function validateCep(mixed $cep): bool
    {
        if (!is_string($cep)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $cep);

        return $digits !== null && strlen($digits) === 8;
    }

    public static function validatePaymentType(mixed $type): bool
    {
        return is_string($type) && in_array($type, ['debito', 'credito'], true);
    }

    public static function validateCardNumber(mixed $number): bool
    {
        if (!is_string($number)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $number);
        if ($digits === null || strlen($digits) < 13 || strlen($digits) > 19) {
            return false;
        }

        return self::luhnCheck($digits);
    }

    public static function validateCardExpiry(mixed $expiry): bool
    {
        if (!is_string($expiry) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
            return false;
        }

        [$month, $year] = explode('/', $expiry);
        $expiryDate = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            sprintf('20%s-%s-01', $year, $month)
        );
        if ($expiryDate === false) {
            return false;
        }

        $expiryDate = $expiryDate->modify('last day of this month');

        return $expiryDate >= new \DateTimeImmutable('today');
    }

    public static function validateCvv(mixed $cvv): bool
    {
        if (!is_string($cvv)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $cvv);
        if ($digits === null) {
            return false;
        }

        return strlen($digits) >= 3 && strlen($digits) <= 4;
    }

    private static function luhnCheck(string $digits): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }

        return $sum % 10 === 0;
    }
}