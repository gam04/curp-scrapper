<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

readonly class Entidad
{
    public function __construct(
        private string $claveEntidad,
        private string $nacionalidad,
        private string $entidad,
    ) {
    }

    public function getClaveEntidad(): string
    {
        return $this->claveEntidad;
    }

    public function getNacionalidad(): string
    {
        return $this->nacionalidad;
    }

    public function getEntidad(): string
    {
        return $this->entidad;
    }
}
