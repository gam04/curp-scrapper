<?php

declare(strict_types=1);

namespace Gam\Test\CurpScrapper;

use InvalidArgumentException;
use JsonException;
use Ramsey\Dev\Tools\TestCase as BaseTestCase;

use function file_exists;
use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * A base test case for common test functionality
 */
class TestCase extends BaseTestCase
{
    protected static function getFileContents(string $name): string
    {
        $fullPath = __DIR__ . '/_files/' . $name;
        if (!file_exists($fullPath)) {
            throw new InvalidArgumentException("the file $name does not exist");
        }

        return (string) file_get_contents($fullPath);
    }

    /**
     * @return array{
     *     proxy: array{ user: string, password: string, host: string, port: int},
     *     case: array<int, array{0: string, 1: string}>
     *     }
     *
     * @throws JsonException
     */
    protected static function getTestConfig(): array
    {
        /**
         * @var array{
         *      proxy: array{ user: string, password: string, host: string, port: int},
         *      case: array<int, array{0: string, 1: string}>
         *      }
         */
        return json_decode(
            self::getFileContents('test_suite.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }
}
