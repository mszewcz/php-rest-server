<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer;

use MS\LightFramework\Base as FrameworkBase;
use MS\LightFramework\Config\AbstractConfig;
use MS\LightFramework\Config\Factory;
use MS\LightFramework\Filesystem\File;
use MS\LightFramework\Filesystem\FileName;
use MS\LightFramework\Variables\Variables;

/**
 * @codeCoverageIgnore
 */
class Base
{
    /**
     * @var Base
     */
    private static $instance;
    /**
     * @var array
     */
    private $controllers = [];
    /**
     * @var string
     */
    private $apiBrowserUri = '/api-browser';
    /**
     * @var string
     */
    private $definitionsDir = '%DOCUMENT_ROOT%/definitions/';
    /**
     * @var string
     */
    private $mapFilePath = '';

    /**
     * Server constructor
     */
    private function __construct()
    {
        $frameworkBase = FrameworkBase::getInstance();
        $variablesHandler = Variables::getInstance();
        $config = Factory::read($variablesHandler->env->get('CONFIG_FILE_SERVER'));

        $this->controllers = $config->controllers;
        $this->apiBrowserUri = (string)$config->apiBrowserUri;
        $this->definitionsDir = $frameworkBase->parsePath((string)$config->definitionsDirectory);
    }

    /**
     * Creates Base object if needed and returns it
     *
     * @return Base
     */
    public static function getInstance(): Base
    {
        if (!isset(static::$instance)) {
            $class = __CLASS__;
            static::$instance = new $class;
        }
        return static::$instance;
    }

    /**
     * __clone overload
     */
    public function __clone()
    {
        throw new \RuntimeException('Clone of Config is not allowed.');
    }

    /**
     * @param $json
     * @return array|\stdClass
     */
    public function decode(string $json)
    {
        return \json_decode($json);
    }

    /**
     * @param $json
     * @return array
     */
    public function decodeAsArray(string $json): array
    {
        return \json_decode($json, true);
    }

    /**
     * @param array $data
     * @return string
     */
    public function encode(array $data): string
    {
        return \json_encode($data, \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function fileExists(string $file): bool
    {
        return File::exists($file);
    }

    /**
     * @param string $file
     * @return string
     */
    public function fileRead(string $file): string
    {
        return File::read($file);
    }

    /**
     * @param string $file
     * @param string $data
     * @return bool
     */
    public function fileWrite(string $file, string $data): bool
    {
        return File::write($file, $data);
    }

    /**
     * @return string
     */
    public function getApiBrowserUri(): string
    {
        return $this->apiBrowserUri;
    }

    /**
     * @return AbstractConfig
     */
    public function getControllers(): AbstractConfig
    {
        return $this->controllers;
    }

    /**
     * @return string
     */
    public function getDefinitionsDir(): string
    {
        return $this->definitionsDir;
    }

    /**
     * Returns path to map file
     *
     * @return string
     */
    public function getMapFilePath(): string
    {
        return $this->mapFilePath;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getSafeFileName(string $file): string
    {
        return FileName::getSafe($file);
    }

    /**
     * Sets controller map
     *
     * @param string $mapFilePath
     */
    public function setMapFilePath(string $mapFilePath): void
    {
        $this->mapFilePath = $mapFilePath;
    }
}
