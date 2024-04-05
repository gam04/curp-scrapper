<?php

declare(strict_types=1);

function random_port(): int
{
    // Ports range from 1 to 65535
    return rand(1, 65535);
}

function browser_app_data(): string
{
    if (getenv('APPDATA') === false && getenv('HOME') === false) {
        throw new RuntimeException('Undefined home variables');
    }

    /**
     * @psalm-suppress PossiblyFalseOperand
     * APPDATA & HOME are evaluated above
     */
    return match (strtolower(PHP_OS_FAMILY)) {
        'windows' => getenv('APPDATA') . DIRECTORY_SEPARATOR . 'undetectedWebDriverPHP',
        'linux' => getenv('HOME') . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'undetectedWebDriverPHP',
        default => '',
    };
}

function delete_directory(string $path): void
{
    /** @var RecursiveIteratorIterator<RecursiveDirectoryIterator> $iterator */
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($path);
}
