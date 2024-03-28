<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

use ValueError;

use function str_starts_with;

enum CurpEstatus: string
{
    case AN = 'Alta Normal';

    case AH = 'Alta con Homonimia';

    case RCN = 'Registro de Cambio No afectando a CURP';

    case RCC = 'Registro de Cambio Afectando a CURP';

    case BD = 'Baja por Defunción';

    case BSU = 'Baja Sin Uso';

    case BAP = 'Baja por Documento Apócrifo';

    case BDM = 'Baja Administrativa';

    case BDP = 'Baja por Adopción';

    case BJD = 'Baja Judicial';

    public function isActive(): bool
    {
        return !str_starts_with($this->name, 'B');
    }

    /**
     * To mirror backed enums tryFrom - returns null on failed match.
     */
    public static function tryFromName(string $name): ?CurpEstatus
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * To mirror backed enums from - throws ValueError on failed match.
     */
    public static function fromName(string $name): CurpEstatus
    {
        $case = self::tryFromName($name);
        if (!$case) {
            throw new ValueError($name . ' is not a valid case for enum ' . self::class);
        }

        return $case;
    }
}
