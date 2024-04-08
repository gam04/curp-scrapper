<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Integration\Renapo;

use Gam\CurpScrapper\Model\Curp;
use Gam\CurpScrapper\Model\CurpEstatus;
use Gam\CurpScrapper\Model\CurpResult;
use Gam\CurpScrapper\Model\Proxy;
use Gam\CurpScrapper\Renapo\Scrapper;
use Gam\CurpScrapper\Renapo\ScrapperException;
use Gam\Test\CurpScrapper\TestCase;
use JsonException;

use function boolval;
use function getenv;

class ScrapperTest extends TestCase
{
    /**
     * @throws JsonException
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CI_BUILD') !== false) {
            $this->markTestSkipped('This test is skipped. Runs only in local build environments');
        }
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     *
     * @throws JsonException
     */
    public static function curpProvider(): array
    {
        return self::getTestConfig()['case'];
    }

    /**
     * @throws ScrapperException
     *
     * @dataProvider curpProvider
     */
    public function testGetCurpDataNoProxy(string $status, string $curp): CurpResult
    {
        $scrapper = new Scrapper(headless: boolval(getenv('HEADLESS', true)));
        $result = $scrapper->getCurpData(new Curp($curp));
        self::assertEquals(CurpEstatus::tryFromName($status), $result->getEstatusCurp());
        $pdf = $scrapper->getPdf($result);
        self::assertNotEmpty($pdf);
        $scrapper->close();

        return $result;
    }

    /**
     * @throws ScrapperException
     * @throws JsonException
     *
     * @dataProvider curpProvider
     */
    public function testGetCurpDataWithProxy(string $status, string $curp): CurpResult
    {
        $config = $this->getTestConfig();
        $scrapper = new Scrapper(
            headless: boolval(getenv('HEADLESS', true)),
            proxy: new Proxy(
                $config['proxy']['host'],
                $config['proxy']['port'],
                $config['proxy']['user'],
                $config['proxy']['password'],
            ),
        );
        $result = $scrapper->getCurpData(new Curp($curp));
        self::assertEquals(CurpEstatus::tryFromName($status), $result->getEstatusCurp());
        $pdf = $scrapper->getPdf($result);
        self::assertNotEmpty($pdf);
        $scrapper->close();

        return $result;
    }

    public function testThrowExceptionOnNonRegisteredCurp(): void
    {
        $scrapper = new Scrapper(headless: boolval(getenv('HEADLESS', true)));
        $this->expectException(ScrapperException::class);
        $this->expectExceptionMessageMatches('*Los datos ingresados no son correctos. Verifica e inténtalo de nuevo*');
        $scrapper->getCurpData(new Curp('RIMF080128HASXNBA1'));
    }
}
