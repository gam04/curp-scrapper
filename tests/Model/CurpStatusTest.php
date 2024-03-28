<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Model;

use Gam\CurpScrapper\Model\CurpEstatus;
use PHPUnit\Framework\TestCase;

class CurpStatusTest extends TestCase
{
    /**
     * @return array<int, array{0: CurpEstatus, 1: bool}>
     */
    public static function statusProvider(): array
    {
        return [
            [CurpEstatus::AH, true],
            [CurpEstatus::AN, true],
            [CurpEstatus::RCC, true],
            [CurpEstatus::RCN, true],
            [CurpEstatus::BAP, false],
            [CurpEstatus::BD, false],
            [CurpEstatus::BDM, false],
            [CurpEstatus::BDP, false],
        ];
    }

    /**
     * @return void
     *
     * @dataProvider statusProvider
     */
    public function testIsActive(CurpEstatus $estatus, bool $isActive)
    {
        self::assertEquals($estatus->isActive(), $isActive);
    }

    public function testFromName(): void
    {
        self::assertEquals(CurpEstatus::BDP, CurpEstatus::tryFromName('BDP'));
        self::assertNull(CurpEstatus::tryFromName('DOES_NOT_EXIST'));
    }
}
