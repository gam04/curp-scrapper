#!/usr/bin/env php
<?php

declare(strict_types=1);

$path = __DIR__ . '/../build/';
$bdiCommand = __DIR__ . '/../vendor/bin/bdi';

if (!file_exists($bdiCommand)) {
    throw new RuntimeException('The bdi package is not installed');
}

/**
 * @psalm-suppress ForbiddenCode
 * There is no user input given for the execution, so could be determinated as 'safe'
 */
shell_exec("$bdiCommand driver:chromedriver $path");

if (file_exists("$path/chromedriver.exe")) {
    echo "\033[0;32m [✔] The driver was downloaded \033[0m";
} else {
    echo "\033[01;31m [🗙] The driver was not downloaded \033[0m";
}
