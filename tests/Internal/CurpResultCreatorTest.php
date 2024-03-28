<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Internal;

use Gam\CurpScrapper\Internal\CurpResultCreator;
use Gam\CurpScrapper\Model\CurpEstatus;
use Gam\Test\CurpScrapper\TestCase;

class CurpResultCreatorTest extends TestCase
{
    public function testMake(): void
    {
        $result = CurpResultCreator::makeFromJson($this->getFileContents('curp_result.json'));
        self::assertEquals('FOO', $result->getCiudadano()->getPrimerApellido());
        self::assertEquals('OAXACA', $result->getEntidad()->getEntidad());
        self::assertEquals(CurpEstatus::RCN, $result->getEstatusCurp());
        self::assertEquals(
            '?curp=HLIP570111HOCFTB15&'
            . 'pcurp=5E7C3AB6F7FCD699C574E05091449216&'
            . 'hash=753f3fbb8ea3327dcd0a80b10f832b620bffa6f0',
            $result->getDownloadQuery(),
        );
        self::assertEmpty($result->getDocumentoProbatorio()->getFoja());
        self::assertEmpty($result->getDocumentoProbatorio()->getTomo());
        self::assertEmpty($result->getDocumentoProbatorio()->getLibro());
        self::assertEquals('00078', $result->getDocumentoProbatorio()->getActa());
    }
}
