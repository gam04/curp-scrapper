<h1 style="text-align: center">gam/curp-scrapper</h1>

<p style="text-align: center">
    <strong>CURP Scrapper</strong>
</p>


<p style="text-align: center">
    <a href="https://github.com/Gam04/curp-scrapper"><img src="https://img.shields.io/badge/source-gam/curp--scrapper-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/gam/curp-scrapper"><img src="https://img.shields.io/packagist/v/gam/curp-scrapper.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/gam/curp-scrapper.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/Gam04/curp-scrapper/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/gam/curp-scrapper.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/Gam04/curp-scrapper/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/Gam04/curp-scrapper/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/Gam04/curp-scrapper"><img src="https://img.shields.io/codecov/c/gh/Gam04/curp-scrapper?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/Gam04/curp-scrapper"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2FGam04%2Fcurp-scrapper%2Fcoverage" alt="Psalm Type Coverage"></a>
</p>


## About

Consulta la CURP 🇲🇽 (Clave Única de Registro de Población) mediante `web scrapping`
al [portal oficial](https://www.gob.mx/curp/). Puedes obtener todos los datos que
ofrece el portal oficial asi como la descarga del `PDF`.

## How it is work?

La consulta de datos funciona mediante web scrapping al
[portal oficial](https://www.gob.mx/curp/) utilizando un webdriver para
la automatización de un navegador basado en Chromium. Se levanta una
instancia `headless` del navegador cambiando algunos parámetros para
que sea indetectable a la  tecnología anti-bot de
[Akamai](https://www.akamai.com/es/products/bot-manager).

Personalmente, no soy fan de utilizar webdrivers/selenium o cualquier otro
software de automatización de pruebas en navegadores para hacer tareas de
scrapping, pero para este caso facilita en gran medida la no detección de
bots de Akamai.

## Installation

Instala este paquete mediante [Composer](https://getcomposer.org).

``` bash
composer require gam/curp-scrapper
```

### Browser & Webdriver Installation

Antes de usar el paquete es necesario instalar un navegador basado en chromium y su
respectivo `webdriver`:

1. Instalar [Chrome](https://www.google.com/chrome/)
   o [Chromium](https://www.chromium.org/getting-involved/download-chromium/)
2. Instalar el correspondiente [webdriver](https://chromedriver.com/) o simplemente
   puedes ejecutar:
   ```shell
   # Instala el driver correspondiente a la version de Chrome/Chromium instalada
   # el driver se mueve a build/
   composer scrapper:driver
   ```

## Usage

Usar la biblioteca es sencillo. Puedes obtener los datos de una `CURP` o descargar el `PDF`.

### Obtener Datos De CURP

```php
<?php

declare(strict_types=1);

/*
 * Si no se especifica la ruta del driver,
 * el paquete utilizara el ubicado en la ruta build/
 * el cual se instala utilizando composer scrapper:driver
 */
$scrapper = new \Gam\CurpScrapper\Renapo\Scrapper();

$data = $scrapper->getCurpData(new \Gam\CurpScrapper\Model\Curp('UZLK580803MVZGIB96'));

/**
 * @var \Gam\CurpScrapper\Model\Ciudadano $ciudadano
 * Obtener datos del ciudadano
 */
$ciudadano = $data->getCiudadano();

/**
 * @var \Gam\CurpScrapper\Model\DocumentoProbatorio $documentoProbatorio
 * Obtener datos del documento como Tomo, Año de registro, Foja, Acta, Libro
 */
$documentoProbatorio = $data->getDocumentoProbatorio();

/**
 * @var \Gam\CurpScrapper\Model\CurpEstatus $status
 * Consulte los posibles estatus de una CURP
 */
$status = $data->getEstatusCurp();

```

### Obtener PDF De CURP

Existen 2 formas de obtener el PDF de una CURP

- **Mediante el resultado de una búsqueda**: Se da como parámetro un objeto
  de tipo `CurpResult` el cual es resultado de una búsqueda mediante el método
  `getCurpData`.

- **Directamente desde una CURP**: Se da como parámetro un objeto de tipo `Curp`.

```php
<?php

declare(strict_types=1);

$scrapper = new \Gam\CurpScrapper\Renapo\Scrapper();

/*
 * Obtener PDF desde el resultado de una busqueda
 */
$data = $scrapper->getCurpData(new \Gam\CurpScrapper\Model\Curp('UZLK580803MVZGIB96'));
$pdf = $scrapper->getPdf($data);


/*
 * Obtener PDF desde una CURP
 */
 $pdf = $scrapper->getPdfFromCurp(new \Gam\CurpScrapper\Model\Curp('UZLK580803MVZGIB96'));

```

**NOTA: En ambos escenarios se hace el mismo número de solicitudes HTTP al portal web**

### Estatus De CURP

- `AN`: Alta Normal
- `AH`: Alta con Homonimia
- `RCN`: Registro de Cambio No afectando a CURP
- `RCC`: Registro de Cambio Afectando a CURP
- `BD`: Baja por Defunción
- `BSU`: Baja Sin Uso
- `BAP`: Baja por Documento Apócrifo
- `BDM`: Baja Administrativa
- `BDP`: Baja por Adopción
- `BJD`: Baja Judicial

### Proxy

Si bien puedes obtener los datos de una CURP sin ninguna accion adicional,
cuando realizas más de 15 solicitudes en menos de 1 minuto*, la tecnología
de Akamai bloquera tus posteriores peticiones y obtendras el siguiente
mensaje de error: `El servicio no está disponible`

Esto se debe a que, la tecnología de Akamai, basa parte de su detección de bots
en un rate-limit y tambien determina si la solicitud proviene de un pool de
direcciones IP que pertenezcan a proxies publicos, cloud providers o en general,
IP con mala reputación.

Por lo anterior, es recomendable establecer un Proxy Residencial:

```php

// sin autenticacion
$scrapper = new \Gam\CurpScrapper\Renapo\Scrapper(
    proxy: new \Gam\CurpScrapper\Model\Proxy('127.0.0.1', 8383)
);

// con autenticacion
$scrapper = new \Gam\CurpScrapper\Renapo\Scrapper(
    proxy: new \Gam\CurpScrapper\Model\Proxy('127.0.0.1', 8383, 'foo', 'bar')
);
```
<!--
**Nota: Al parecer en Chromium no es posible especificar Proxy (`--proxy-server`)
con autenticación. Puedes consultar el siguiente
[enlace](https://issues.chromium.org/issues/40471183) para más información**

Una solución para este escenario es encadenar proxies (`upstream proxy - chain proxies`)
es decir, establecer un proxy local sin autenticación _encima_ del proxy objetivo.
Puedes utilizar [mitmproxy](https://docs.mitmproxy.org/stable/concepts-modes/#upstream-proxy)
para este proposito.
-->

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.


## Copyright and License

gam/curp-scrapper is copyright © [Antonio Gamboa](https://somegamboapage.com).
All rights reserved.

## TODO

- Code Coverage
