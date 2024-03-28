<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

class Proxy
{
    public function __construct(
        public string $ip,
        public int $port,
        public ?string $username = null,
        public ?string $password = null,
    ) {
    }

    /**
     * @return array{
     *     'proxyType': string,
     *     'httpProxy': string,
     *     'sslProxy': string,
     * }
     */
    public function getProxyOptions(): array
    {
        $host = isset($this->username, $this->password)
            ? "{$this->username}:{$this->password}@{$this->ip}:{$this->port}"
            : "{$this->ip}:{$this->port}";

        return [
            'proxyType' => 'manual',
            'httpProxy' => "http://$host",
            'sslProxy' => "https://$host",
        ];
    }
}
