<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Renapo;

use Gam\CurpScrapper\Renapo\Scrapper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScrapperTest extends TestCase
{
    public function testThrowExceptionOnInvalidDriverPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Scrapper('/does/not/exist', false);
    }

    public function testNoProxyList(): void
    {
        self::assertEquals([
            'accounts.google.com',
            '*googleapis.com',
            '*doubleclick.net',
            'analytics.google.com',
            '*.gif',
            'www.google.com.mx',
            'www.google-analytics.com',
            '*.png',
        ], Scrapper::NO_PROXY_LIST);
    }
}
