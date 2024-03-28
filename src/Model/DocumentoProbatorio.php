<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

readonly class DocumentoProbatorio
{
    public function __construct(
        private string $tomo,
        private int $anioRegistro,
        private string $foja,
        private string $acta,
        private string $libro,
        private EntidadRegistro $entidad,
    ) {
    }

    public function getTomo(): string
    {
        return $this->tomo;
    }

    public function getAnioRegistro(): int
    {
        return $this->anioRegistro;
    }

    public function getFoja(): string
    {
        return $this->foja;
    }

    public function getActa(): string
    {
        return $this->acta;
    }

    public function getLibro(): string
    {
        return $this->libro;
    }

    public function getEntidad(): EntidadRegistro
    {
        return $this->entidad;
    }
}
