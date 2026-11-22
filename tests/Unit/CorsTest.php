<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Cors;
use PHPUnit\Framework\TestCase;

final class CorsTest extends TestCase
{
    public function testAllowedOriginsParse(): void
    {
        putenv('CORS_ALLOWED_ORIGINS=http://a.com, http://b.com');
        $_ENV['CORS_ALLOWED_ORIGINS'] = 'http://a.com, http://b.com';

        self::assertSame(['http://a.com', 'http://b.com'], Cors::allowedOrigins());
    }
}