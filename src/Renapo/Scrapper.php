<?php

declare(strict_types=1);

namespace Gam\CurpScrapper\Renapo;

use Exception;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Gam\CurpScrapper\Internal\CurpResultCreator;
use Gam\CurpScrapper\Internal\ProxyExtensionCreator;
use Gam\CurpScrapper\Model\Curp;
use Gam\CurpScrapper\Model\CurpResult;
use Gam\CurpScrapper\Model\Proxy;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Panther\Client;
use Throwable;

use function array_push;
use function base64_decode;
use function browser_app_data;
use function count;
use function delete_directory;
use function file_exists;
use function file_get_contents;
use function implode;
use function is_null;
use function random_port;

use const DIRECTORY_SEPARATOR;

class Scrapper
{
    public const MAIN_URL = 'https://www.gob.mx/curp';

    public const PDF_URL = 'https://consultas.curp.gob.mx/CurpSP/pdfgobmx';

    private Client $client;

    private const NO_PROXY_LIST = [
        'accounts.google.com',
        '*googleapis.com',
        '*doubleclick.net',
        'analytics.google.com',
        '*.gif',
        'www.google.com.mx',
        'www.google-analytics.com',
        '*.png',
    ];

    private const DEFAULT_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
    . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0';

    private ?string $extensionPath;

    private string $userAgent;

    private ChromeDevToolsDriver $devTools;

    public function __construct(
        string $driverPath = __DIR__ . '/../../build/chromedriver.exe',
        bool $headless = true,
        ?Proxy $proxy = null,
        ?string $userAgent = null,
    ) {
        if (!file_exists($driverPath)) {
            throw new InvalidArgumentException(
                'The specified driver does not exist. Hint: Run composer install-driver',
            );
        }
        $this->extensionPath = null;
        $this->userAgent = $userAgent ?? self::DEFAULT_UA;
        $this->client = $this->createClient($driverPath, $headless, $proxy);
        /** @var RemoteWebDriver $driver */
        $driver = $this->client->getWebDriver();
        $this->devTools = new ChromeDevToolsDriver($driver);
        $this->start();
    }

    private function start(): void
    {
        $this->client->start();
        try {
            $this->client->executeScript("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})");
        } catch (Throwable) {
        }
    }

    private function cdpSettings(): void
    {
        $getPropsJS = <<<'JS'
        let objectToInspect = window
        result = [];
        while (objectToInspect !== null) {
            result = result.concat(Object.getOwnPropertyNames(objectToInspect));
            objectToInspect = Object.getPrototypeOf(objectToInspect);
        }
        return result.filter(i => i.match(/.+_.+_(Array|Promise|Symbol)/ig))
        JS;

        $deleteCdcJS = <<<'JS'
        let objectToInspect = window,
        result = [];
        while (objectToInspect !== null) {
            result = result.concat(Object.getOwnPropertyNames(objectToInspect));
            objectToInspect = Object.getPrototypeOf(objectToInspect);
        }
        result.forEach(p => p.match(/.+_.+_(Array|Promise|Symbol)/ig) && delete window[p] && console.log('removed', p))
        JS;

        try {
            /** @var string[]|null $props */
            $props = $this->client->executeScript($getPropsJS);

            if ($props !== null && count($props) >= 1) {
                $this->devTools->execute('Page.addScriptToEvaluateOnNewDocument', ['source' => $deleteCdcJS]);
            }

            $this->devTools->execute(
                'Page.addScriptToEvaluateOnNewDocument',
                ['source' => file_get_contents(__DIR__ . '/../../resources/stealth.min.js')],
            );
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 10, $e);
        }
    }

    private function headless(): void
    {
        $noWebDriverJS = <<<'JS'
        Object.defineProperty(window, 'navigator', {
        value: new Proxy(navigator, {
            has: (target, key) => (key === 'webdriver' ? false : key in target),
            get: (target, key) =>
                key === 'webdriver' ?
                    false :
                    typeof target[key] === 'function' ?
                        target[key].bind(target) :
                        target[key]
            })
        });
        JS;

        $this->devTools->execute('Page.addScriptToEvaluateOnNewDocument', ['source' => $noWebDriverJS,]);
        $this->devTools->execute('Network.setUserAgentOverride', ['userAgent' => $this->userAgent]);
        $this->devTools->execute(
            'Page.addScriptToEvaluateOnNewDocument',
            ['source' => "Object.defineProperty(navigator, 'maxTouchPoints', {get: () => 1});"],
        );

        $this->devTools->execute(
            'Page.addScriptToEvaluateOnNewDocument',
            ['source' => file_get_contents(__DIR__ . '/../../resources/evasions.js')],
        );
    }

    /**
     * @throws ScrapperException
     */
    public function getCurpData(Curp $curp): CurpResult
    {
        $this->headless();
        $this->cdpSettings();
        $this->client->get(self::MAIN_URL)->submitForm('Buscar', ['curp' => $curp->getContent()]);

        try {
            $this->client->waitFor('#download');

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
             * }}} $apiResponse The array containing the data structure described above.
             */
            $apiResponse = (array) $this->client->executeScript(<<<'JS'
            let a = Ember.Namespace.NAMESPACES[1].__container__.lookup('-view-registry:main')['ember252']['response'];
            return a;
            JS,);

            return CurpResultCreator::make($apiResponse);
        } catch (NoSuchElementException | TimeoutException $e) {
            $error = $this->client->getCrawler()->filter('#errorLog > div')->text();

            throw new ScrapperException(
                "Maybe, the CURP is invalid or the request was blocked. Try again. Msg: $error . {$e->getMessage()}",
            );
            // phpcs:ignore
        } catch (Exception $e) {
            throw new ScrapperException(
                'The JS code was not succesfull executed. Possibly, the RENAPO page has been changed: ' .
                $e->getMessage(),
            );
        }
    }

    /**
     * @throws ScrapperException
     */
    public function getPdfFromCurp(Curp $curp): string
    {
        return $this->getPdf($this->getCurpData($curp));
    }

    /**
     * @throws ScrapperException
     */
    public function getPdf(CurpResult $result): string
    {
        $res = $this->client->get($result->getFullDownloadQuery());
        $htmlBody = $res->getCrawler()->filter('body');

        if ($htmlBody->count() === 0) {
            throw new ScrapperException('Unable to get the PDF. Unexpected HTML response');
        }

        return base64_decode($htmlBody->text());
    }

    public function close(): void
    {
        $this->client->close();
        if (!is_null($this->extensionPath)) {
            delete_directory($this->extensionPath);
        }
    }

    private function createClient(string $driverPath, bool $headless, ?Proxy $proxy = null): Client
    {
        $options = new ChromeOptions();
        $options->setExperimentalOption('excludeSwitches', ['enable-automation']);
        $options->setExperimentalOption('useAutomationExtension', false);

        $dataDir = browser_app_data() . DIRECTORY_SEPARATOR . 'chrome_user-data';

        if (file_exists($dataDir)) {
            delete_directory($dataDir);
        }

        $prefs = [
            'download.default_directory' => __DIR__,
            'download.prompt_for_download' => 'false',
            'extensions.ui.developer_mode' => true,
        ];
        $options->setExperimentalOption('prefs', $prefs);

        $arguments = [
            '--user-data-dir=' . $dataDir,
            '--no-default-browser-check',
            '--no-first-run',
            '--no-service-autorun',
            '--no-sandbox',
            '--log-level=0',
            '--user-agent=' . $this->userAgent,
            '--disable-blink-features=AutomationControlled',
            '--disable-plugins',
            '--disable-dev-shm-usage',
        ];

        if ($headless) {
            array_push($arguments, '--headless=new', '--window-size=1200,1100', '--disable-gpu');
        }

        if ($proxy !== null) {
            $this->setProxyConfig($proxy, $arguments);
        }

        return Client::createChromeClient(
            chromeDriverBinary: $driverPath,
            arguments: $arguments,
            options: [
                'port' => random_port(),
                'capabilites' => [
                    ChromeOptions::CAPABILITY => $options,
                ],
            ],
        );
    }

    /**
     * @param string[] $arguments
     */
    private function setProxyConfig(Proxy $proxy, array &$arguments): void
    {
        // no auth proxy
        if (is_null($proxy->username) && is_null($proxy->password)) {
            array_push(
                $arguments,
                "--proxy-server={$proxy->getProxyOptions()['httpProxy']}",
                '--proxy-bypass-list=' . implode(';', self::NO_PROXY_LIST),
            );
        } else {
            $this->extensionPath = (new ProxyExtensionCreator())($proxy, self::NO_PROXY_LIST);
            // auth proxy with a very tricky (and ugly) workaround
            $arguments[] = '--load-extension=' . $this->extensionPath;
        }
    }
}