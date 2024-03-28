<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Internal;

use Gam\CurpScrapper\Internal\ProxyExtensionCreator;
use Gam\CurpScrapper\Model\Proxy;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

class ProxyExtensionCreatorTest extends TestCase
{
    public function testInvoke(): void
    {
        $path = (new ProxyExtensionCreator())(new Proxy('127.0.0.1', 8383, 'foo', 'bar'));
        self::assertFileExists($path);
        self::assertFileExists($path . '/manifest.json');
        self::assertFileExists($path . '/background.js');

        $expected = <<<'JS'
        const config = {
            mode: "fixed_servers",
            rules: {
                singleProxy: {
                    scheme: "http",
                    host: "127.0.0.1",
                    port: parseInt("8383")
                },
                bypassList: []
            }
        };

        chrome.proxy.settings.set({value: config, scope: "regular"}, function () {});

        function callbackFn(details)
        {
            return {
                authCredentials: {
                    username: "foo",
                    password: "bar"
                }
            };
        }

        chrome.webRequest.onAuthRequired.addListener(
            callbackFn,
            {urls: ["<all_urls>"]},
            ['blocking']
        );

        JS;

        self::assertEquals($expected, file_get_contents($path . '/background.js'));
    }
}
