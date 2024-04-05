<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper\Unit;

use Gam\Test\CurpScrapper\TestCase;

use function browser_app_data;
use function delete_directory;
use function file_put_contents;
use function mkdir;
use function random_port;
use function str_contains;
use function strtolower;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;

class FunctionsTest extends TestCase
{
    public function testRandomPort(): void
    {
        $port = random_port();
        $this->assertGreaterThanOrEqual(1, $port);
        $this->assertLessThanOrEqual(65535, $port);
    }

    public function testBrowserAppData(): void
    {
        $aguja = match (strtolower(PHP_OS_FAMILY)) {
            'linux' => '.local',
            'windows' => 'AppData',
            default => '',
        };

        self::assertTrue(str_contains(browser_app_data(), $aguja));
    }

    public function testDeleteDirectory(): void
    {
        // Creamos una carpeta temporal y algunos archivos dentro de ella para probar la función
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_dir';
        mkdir($tempDir);
        file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'file1.txt', 'Test file 1');
        file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'file2.txt', 'Test file 2');
        mkdir($tempDir . DIRECTORY_SEPARATOR . 'subdir');
        file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'subdir' . DIRECTORY_SEPARATOR . 'file3.txt', 'Test file 3');

        // Ejecutamos la función a probar
        delete_directory($tempDir);

        // Verificamos que la carpeta ya no exista
        self::assertDirectoryDoesNotExist($tempDir);
    }
}
