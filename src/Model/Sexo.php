<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

use InvalidArgumentException;

use function strtoupper;

enum Sexo: string
{
    case HOMBRE = 'HOMBRE';

    case MUJER = 'MUJER';

    public static function fromCommon(string $char): self
    {
        return match (strtoupper($char)) {
            'H', 'MASCULINO' => self::HOMBRE,
            'M', 'FEMENINO' => self::MUJER,
            default => throw new InvalidArgumentException('Invalid value'),
        };
    }
}
