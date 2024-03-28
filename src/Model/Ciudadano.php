<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

use DateTime;

readonly class Ciudadano
{
    public function __construct(
        private string $segundoApellido,
        private string $primerApellido,
        private string $nombres,
        private Sexo $sexo,
        private DateTime $fechaNacimiento,
    ) {
    }

    public function getSegundoApellido(): string
    {
        return $this->segundoApellido;
    }

    public function getPrimerApellido(): string
    {
        return $this->primerApellido;
    }

    public function getNombres(): string
    {
        return $this->nombres;
    }

    public function getSexo(): Sexo
    {
        return $this->sexo;
    }

    public function getFechaNacimiento(): DateTime
    {
        return $this->fechaNacimiento;
    }
}
