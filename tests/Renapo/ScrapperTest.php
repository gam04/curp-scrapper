<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Renapo;

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
    private Scrapper $scrapper;

    /**
     * @throws JsonException
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CI_BUILD') !== false) {
            $this->markTestSkipped('This test is skipped. Runs only in local build environments');
        }

        $config = self::getTestConfig();
        if (isset($config['proxy'])) {
            $this->scrapper = new Scrapper(
                headless: boolval(getenv('HEADLESS', true)),
                proxy: new Proxy(
                    $config['proxy']['host'],
                    $config['proxy']['port'],
                    $config['proxy']['user'],
                    $config['proxy']['password'],
                ),
            );
        } else {
            $this->scrapper = new Scrapper(headless: boolval(getenv('HEADLESS', true)));
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
    public function testGetCurpData(string $status, string $curp): CurpResult
    {
        $result = $this->scrapper->getCurpData(new Curp($curp));
        self::assertEquals(CurpEstatus::tryFromName($status), $result->getEstatusCurp());
        $pdf = $this->scrapper->getPdf($result);
        self::assertNotEmpty($pdf);
        $this->scrapper->close();

        return $result;
    }
}
