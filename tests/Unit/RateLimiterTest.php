<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\ConnectionFactory;
use App\Security\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
    }

    public function testPermiteAteMaximo(): void
    {
        $limiter = new RateLimiter();
        self::assertTrue($limiter->allow('test-bucket', 3, 3600));
        self::assertTrue($limiter->allow('test-bucket', 3, 3600));
        self::assertTrue($limiter->allow('test-bucket', 3, 3600));
        self::assertFalse($limiter->allow('test-bucket', 3, 3600));
    }
}