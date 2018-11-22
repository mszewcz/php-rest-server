<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Localization;

use MS\LightFramework\Base as FrameworkBase;
use MS\LightFramework\Config\Config;
use MS\LightFramework\Config\Factory;
use MS\LightFramework\Filesystem\Directory;
use MS\LightFramework\Filesystem\File;
use MS\LightFramework\Variables\Variables;


final class LocalizationService
{
    /**
     * @var LocalizationService
     */
    private static $instance;
    /**
     * @var FrameworkBase
     */
    private $frameworkBase;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var string
     */
    private $defaultLanguage = 'pl_PL';
    /**
     * @var null
     */
    private $currentLanguage = null;
    /**
     * @var array
     */
    private $languages = [];
    /**
     * @var array
     */
    private $translations = [];

    /**
     * LocalizationService constructor.
     */
    private function __construct()
    {
        $variables = Variables::getInstance();

        $this->frameworkBase = FrameworkBase::getInstance();
        $this->config = Factory::read($variables->env->get('CONFIG_FILE_SERVER', Variables::TYPE_STRING));
        $this->defaultLanguage = (string) $this->config->localization->defaultLanguage;

        $languages = $this->config->localization->languages;
        foreach ($languages as $language) {
            $this->languages[(string) $language->code] = (string) $language->directory;
        }
    }

    /**
     * Creates ApiBase object if needed and returns it
     *
     * @return LocalizationService
     */
    public static function getInstance(): LocalizationService
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
     * @param string $language
     */
    public function setLanguage($language = 'pl_PL'): void
    {
        if (array_key_exists($language, $this->languages)) {
            $this->currentLanguage = $language;
            $this->translations = [];

            $langDir = $this->frameworkBase->parsePath($this->languages[$language]);
            $langDirReadResult = Directory::read($langDir);

            foreach ($langDirReadResult['files'] as $file) {
                $translations = File::read($file);
                if ($translations !== false) {
                    $translations = json_decode($translations, true);
                    $this->translations = array_merge($this->translations, $translations);
                }
            }
        }
    }

    /**
     * @param string $text
     * @return string
     */
    public function text(string $text): string
    {
        if ($this->currentLanguage === null) {
            $this->setLanguage($this->defaultLanguage);
        }

        $textParts = explode('.', $text);
        $result = $this->translations;

        try {
            foreach ($textParts as $part) {
                if (!isset($result[$part])) {
                    $message = sprintf('Missing translation for %s', $text);
                    throw new \Exception($message);
                }
                $result = $result[$part];
            }
            return (string) $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
