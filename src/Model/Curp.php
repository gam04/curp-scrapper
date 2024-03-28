<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

use InvalidArgumentException;

use function preg_match;

class Curp
{
    private string $content;

    public function __construct(string $value)
    {
        if (
            preg_match(
                '#[A-Z][AEIOU][A-Z]{2}[0-9]{2}(?:0[1-9]|1[0-2])'
                . '(?:[1-2][0-9]|0[1-9]|3[0-1])[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}(?:[0-9]|[A-Z])[0-9]#',
                $value,
            ) === false
        ) {
            throw new InvalidArgumentException("$value is not a valid CURP");
        }
        $this->content = $value;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
