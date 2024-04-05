<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Internal;

use DateTime;
use Gam\CurpScrapper\Model\Ciudadano;
use Gam\CurpScrapper\Model\Curp;
use Gam\CurpScrapper\Model\CurpEstatus;
use Gam\CurpScrapper\Model\CurpResult;
use Gam\CurpScrapper\Model\DocumentoProbatorio;
use Gam\CurpScrapper\Model\Entidad;
use Gam\CurpScrapper\Model\EntidadRegistro;
use Gam\CurpScrapper\Model\Sexo;
use InvalidArgumentException;
use JsonException;

use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
class CurpResultCreator
{
    /**
     * @throws JsonException
     */
    public static function makeFromJson(string $contents): CurpResult
    {
        /**
         * @var array{mensaje: string, codigo: string, registros: array{0: array{
         *   parametro: string,
         *   fechaNacimiento: string,
         *   docProbatorio: int,
         *   segundoApellido: string,
         *   curp: string,
         *   nombres: string,
         *   primerApellido: string,
         *   sexo: string,
         *   claveEntidad: string,
         *   statusCurp: string,
         *   nacionalidad: string,
         *   entidad: string,
         *   datosDocProbatorio: array{
         *     entidadRegistro: string,
         *     tomo: string,
         *     claveMunicipioRegistro: string,
         *     anioReg: string,
         *     claveEntidadRegistro: string,
         *     foja: string,
         *     numActa: string,
         *     libro: string,
         *     municipioRegistro: string
         *   }
         * }}} $data The array containing the data structure described above.
         */
        $data = (array) json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return self::make($data);
    }

    /**
     * @param array{mensaje: string, codigo: string, registros: array{0: array{
     *   parametro: string,
     *   fechaNacimiento: string,
     *   docProbatorio: int,
     *   segundoApellido: string,
     *   curp: string,
     *   nombres: string,
     *   primerApellido: string,
     *   sexo: string,
     *   claveEntidad: string,
     *   statusCurp: string,
     *   nacionalidad: string,
     *   entidad: string,
     *   datosDocProbatorio: array{
     *     entidadRegistro: string,
     *     tomo: string,
     *     claveMunicipioRegistro: string,
     *     anioReg: string,
     *     claveEntidadRegistro: string,
     *     foja: string,
     *     numActa: string,
     *     libro: string,
     *     municipioRegistro: string
     *   }
     * }}} $data The array containing the data structure described above.
     */
    public static function make(array $data): CurpResult
    {
        if ($data['codigo'] !== '01') {
            throw new InvalidArgumentException('The RENAPO payload must contain: codigo=01');
        }

        $registro = $data['registros'][0];
        /** @var DateTime $fechaNacimiento */
        $fechaNacimiento = DateTime::createFromFormat('d/m/Y', $registro['fechaNacimiento']);

        /** @var array<string, string> $docProbatorio */
        $docProbatorio = $registro['datosDocProbatorio'];

        $ciudadano = new Ciudadano(
            segundoApellido: $registro['segundoApellido'],
            primerApellido: $registro['primerApellido'],
            nombres: $registro['nombres'],
            sexo: Sexo::from($registro['sexo']),
            fechaNacimiento: $fechaNacimiento,
        );

        $entidad = new Entidad(
            claveEntidad: $registro['claveEntidad'],
            nacionalidad: $registro['nacionalidad'],
            entidad: $registro['entidad'],
        );

        $probatorio = new DocumentoProbatorio(
            $docProbatorio['tomo'],
            (int) $docProbatorio['anioReg'],
            $docProbatorio['foja'],
            $docProbatorio['numActa'],
            $docProbatorio['libro'],
            new EntidadRegistro(
                $docProbatorio['entidadRegistro'],
                $docProbatorio['claveMunicipioRegistro'],
                $docProbatorio['claveEntidadRegistro'],
                $docProbatorio['municipioRegistro'],
            ),
        );

        return new CurpResult(
            ciudadano: $ciudadano,
            curp: new Curp($registro['curp']),
            estatusCurp: CurpEstatus::fromName($registro['statusCurp']),
            entidad: $entidad,
            documentoProbatorio: $probatorio,
            downloadQuery: $registro['parametro'],
        );
    }
}
