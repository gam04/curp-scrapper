<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Model;

use Gam\CurpScrapper\Model\Proxy;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @covers \Gam\CurpScrapper\Model\Proxy::getProxyOptions
     */
    public function testNonAuthProxyOptions(): void
    {
        $proxy = new Proxy('127.0.0.1', 8080, 'foo', 'bar');

        self::assertEquals([
            'proxyType' => 'manual',
            'httpProxy' => 'http://foo:bar@127.0.0.1:8080',
            'sslProxy' => 'https://foo:bar@127.0.0.1:8080',
        ], $proxy->getProxyOptions());
    }

    /**
     * @covers \Gam\CurpScrapper\Model\Proxy::getProxyOptions
     */
    public function testAuthProxyOptions(): void
    {
        $proxy = new Proxy('127.0.0.1', 8080);

        self::assertEquals([
            'proxyType' => 'manual',
            'httpProxy' => 'http://127.0.0.1:8080',
            'sslProxy' => 'https://127.0.0.1:8080',
        ], $proxy->getProxyOptions());
    }
}
