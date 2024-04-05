<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Internal;

use Gam\CurpScrapper\Internal\CurpResultCreator;
use Gam\CurpScrapper\Model\CurpEstatus;
use Gam\CurpScrapper\Model\CurpResult;
use Gam\Test\CurpScrapper\TestCase;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\UsesClass;

use function json_decode;

use const JSON_THROW_ON_ERROR;

#[UsesClass(CurpResult::class)]
class CurpResultCreatorTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testMakeFromJson(): void
    {
        $result = CurpResultCreator::makeFromJson($this->getFileContents('curp_result.json'));
        self::assertNotEmpty($result);
    }

    public function testThrowExceptionOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);
        CurpResultCreator::makeFromJson('INVALID');
    }

    /**
     * @throws JsonException
     */
    public function testMake(): void
    {
        $data = (array) json_decode(
            $this->getFileContents('curp_result.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @phpstan-ignore-next-line
         */
        $result = CurpResultCreator::make($data);
        self::assertEquals('FOO', $result->getCiudadano()->getPrimerApellido());
        self::assertEquals('OAXACA', $result->getEntidad()->getEntidad());
        self::assertEquals(CurpEstatus::RCN, $result->getEstatusCurp());
        self::assertEquals(
            '?curp=PEPJ190228HASRRVA0&'
            . 'pcurp=5E7C3AB6F7FCD699C574E05091449216&'
            . 'hash=753f3fbb8ea3327dcd0a80b10f832b620bffa6f0',
            $result->getDownloadQuery(),
        );
        self::assertEmpty($result->getDocumentoProbatorio()->getFoja());
        self::assertEmpty($result->getDocumentoProbatorio()->getTomo());
        self::assertEmpty($result->getDocumentoProbatorio()->getLibro());
        self::assertEquals('00078', $result->getDocumentoProbatorio()->getActa());
    }

    public function testThrowExceptionOnInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @phpstan-ignore-next-line
         */
        CurpResultCreator::make(['codigo' => '02']);
    }
}
