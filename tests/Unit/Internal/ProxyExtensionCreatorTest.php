<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit\Internal;

use Gam\CurpScrapper\Internal\ProxyExtensionCreator;
use Gam\CurpScrapper\Model\Proxy;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class ProxyExtensionCreatorTest extends TestCase
{
    public function testCreateManifestCreationFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $creator = new ProxyExtensionCreator();
        $path = sys_get_temp_dir() . '/' . uniqid();

        // Simulate failure in creating manifest.json
        mkdir($path);
        file_put_contents($path . '/manifest.json', ''); // Create an empty file intentionally to cause failure

        $creator->__invoke(new Proxy('127.0.0.1', 8383, 'foo', 'bar'), $path);
    }

    public function testCreateEmptyByPassList(): void
    {
        $creator = new ProxyExtensionCreator();
        $path = sys_get_temp_dir() . '/' . uniqid();
        $creator->__invoke(new Proxy('127.0.0.1', 8383, 'foo', 'bar'), $path, []);
        self::assertFileExists($path);
        self::assertFileExists($path . '/manifest.json');
        self::assertFileExists($path . '/background.js');

        // Assert that bypassList is empty in background.js
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

    public function testCreateWithByPassList(): void
    {
        $creator = new ProxyExtensionCreator();
        $path = sys_get_temp_dir() . '/' . uniqid();
        $creator->__invoke(new Proxy('127.0.0.1', 8383, 'foo', 'bar'), $path, ['foo.com']);
        self::assertFileExists($path);
        self::assertFileExists($path . '/manifest.json');
        self::assertFileExists($path . '/background.js');

        // Assert that bypassList is empty in background.js
        $expected = <<<'JS'
    const config = {
        mode: "fixed_servers",
        rules: {
            singleProxy: {
                scheme: "http",
                host: "127.0.0.1",
                port: parseInt("8383")
            },
            bypassList: ['foo.com']
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
