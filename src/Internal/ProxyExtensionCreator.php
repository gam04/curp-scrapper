<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Internal;

use Gam\CurpScrapper\Model\Proxy;
use RuntimeException;

use function file_get_contents;
use function file_put_contents;
use function implode;
use function mkdir;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;

/**
 * @internal
 *
 * @see https://github.com/RobinDev/Selenium-Chrome-HTTP-Private-Proxy
 */
class ProxyExtensionCreator
{
    /**
     * @param string[] $byPassList
     */
    public function __invoke(Proxy $proxy, array $byPassList = []): string
    {
        $unpacked = sys_get_temp_dir() . '/' . uniqid();

        $noProxyList = $byPassList === [] ? '' : "'" . implode("','", $byPassList) . "'";

        if (!mkdir($unpacked)) {
            throw new RuntimeException('Unable to create the directory');
        }
        file_put_contents(
            $unpacked . '/manifest.json',
            (string) file_get_contents(__DIR__ . '/../../resources/manifest.json'),
        );
        $background = (string) file_get_contents(__DIR__ . '/../../resources/background.js');
        $background = str_replace(
            ['%proxy_host', '%proxy_port', '%username', '%password', '"%bypass_list"'],
            [$proxy->ip, $proxy->port, $proxy->username ?? '', $proxy->password ?? '', $noProxyList],
            $background,
        );
        file_put_contents($unpacked . '/background.js', $background);

        return $unpacked;
    }
}
