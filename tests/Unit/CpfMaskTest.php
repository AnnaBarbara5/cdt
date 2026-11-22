<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CpfMask;
use PHPUnit\Framework\TestCase;

final class CpfMaskTest extends TestCase
{
    public function testMascaraCpf(): void
    {
        self::assertSame('529.***.***-25', CpfMask::mask('529.982.247-25'));
    }
}