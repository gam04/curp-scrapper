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

    public function testThrowExceptionOnInvalidDataDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Scrapper(dataDir: '/does/not/exist');
    }

    public function testGetUserAgent(): void
    {
        $s = new Scrapper();
        self::assertEquals(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            $s->getUserAgent(),
        );
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
