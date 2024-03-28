<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Model;

use Gam\CurpScrapper\Renapo\Scrapper;

readonly class CurpResult
{
    /**
     * @internal
     */
    public function __construct(
        private Ciudadano $ciudadano,
        private Curp $curp,
        private CurpEstatus $estatusCurp,
        private Entidad $entidad,
        private DocumentoProbatorio $documentoProbatorio,
        private string $downloadQuery,
    ) {
    }

    public function getCiudadano(): Ciudadano
    {
        return $this->ciudadano;
    }

    public function getDownloadQuery(): string
    {
        return $this->downloadQuery;
    }

    public function getFullDownloadQuery(): string
    {
        return Scrapper::PDF_URL . $this->getDownloadQuery();
    }

    public function getCurp(): Curp
    {
        return $this->curp;
    }

    public function getEstatusCurp(): CurpEstatus
    {
        return $this->estatusCurp;
    }

    public function getEntidad(): Entidad
    {
        return $this->entidad;
    }

    public function getDocumentoProbatorio(): DocumentoProbatorio
    {
        return $this->documentoProbatorio;
    }
}
