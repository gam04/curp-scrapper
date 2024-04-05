<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Model;

use Gam\CurpScrapper\Model\Sexo;
use PHPUnit\Framework\TestCase;

class SexoTest extends TestCase
{
    /**
     * @return array<int,array<int, Sexo|string>>
     */
    public static function sexoProvider(): array
    {
        return [
            ['H', Sexo::HOMBRE],
            ['MASCULINO', Sexo::HOMBRE],
            ['M', Sexo::MUJER],
            ['FEMENINO', Sexo::MUJER],
        ];
    }

    /**
     * @dataProvider sexoProvider
     */
    public function testFromCommon(string $case, Sexo $expected): void
    {
        self::assertEquals($expected, Sexo::fromCommon($case));
    }
}
