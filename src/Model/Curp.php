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
            preg_match('#^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])'
                . '[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)'
                . '[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$#', $value) === 0
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
