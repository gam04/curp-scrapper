<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Model;

use Gam\CurpScrapper\Model\Curp;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Curp::class)]
class CurpTest extends TestCase
{
    public function testThrowsExceptionOnInvalidCurpValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Curp('AAA010101AAA');
    }

    public function testDoesNotThrowExceptionOnValidCurpValue(): void
    {
        $curp = new Curp('RIMF080128HASXNBA1');
        self::assertNotEmpty($curp->getContent());
    }
}
