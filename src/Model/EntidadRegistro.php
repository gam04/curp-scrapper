<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

readonly class EntidadRegistro
{
    public function __construct(
        private string $entidad,
        private string $claveMunicipio,
        private string $claveEntidad,
        private string $municipio,
    ) {
    }

    public function getEntidad(): string
    {
        return $this->entidad;
    }

    public function getClaveMunicipio(): string
    {
        return $this->claveMunicipio;
    }

    public function getClaveEntidad(): string
    {
        return $this->claveEntidad;
    }

    public function getMunicipio(): string
    {
        return $this->municipio;
    }
}
