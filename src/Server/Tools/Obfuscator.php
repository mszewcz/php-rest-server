<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Tools;

use MS\LightFramework\Base;
use MS\LightFramework\Config\Factory;
use MS\LightFramework\Filesystem\File;
use MS\LightFramework\Variables\Variables;


final class Obfuscator
{
    /**
     * @var \MS\LightFramework\Base
     */
    private $frameworkBase;
    /**
     * @var \MS\LightFramework\Config\Config
     */
    private $config;

    /**
     * IntegrityToken constructor.
     */
    public function __construct()
    {
        $this->frameworkBase = Base::getInstance();

        $variablesHandler = Variables::getInstance();
        $this->config = Factory::read($variablesHandler->env->get('CONFIG_FILE_SERVER'));

        $this->createKeyFile();
        $this->createVectorFile();
    }

    /**
     * @param $data
     * @param $table
     * @param $userId
     * @return string
     */
    public function obfuscate($data, $table, $userId): string
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('openssl')) {
            return (string) $data;
        }
        if (!$this->config->obfuscator->enabled) {
            return (string) $data;
        }
        if (!$this->config->obfuscator->userDependent) {
            $userId = 0;
        }
        // @codeCoverageIgnoreEnd
        // set-up encryption
        $cip = (string) $this->config->obfuscator->cipher;
        $key = File::read($this->frameworkBase->parsePath((string) $this->config->obfuscator->keyFile));
        $key = $this->base64UrlDecode($key);
        $vec = File::read($this->frameworkBase->parsePath((string) $this->config->obfuscator->vectorFile));
        $vec = $this->base64UrlDecode($vec);
        // encrypt
        $val = sprintf('%s.%s.%s', (string) $table, (string) $data, (string) $userId);
        $raw = openssl_encrypt($val, $cip, $key, OPENSSL_RAW_DATA, $vec);
        $hmc = hash_hmac('sha256', $raw, $key, true);

        return $this->base64UrlEncode($hmc . $raw);
    }

    /**
     * @param $data
     * @param $table
     * @param $userId
     * @return string
     */
    public function deobfuscate($data, $table, $userId): string
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('openssl')) {
            return (string) $data;
        }
        if (!$this->config->obfuscator->enabled) {
            return (string) $data;
        }
        if (!$this->config->obfuscator->userDependent) {
            $userId = 0;
        }
        // @codeCoverageIgnoreEnd
        // set-up encryption
        $cip = (string) $this->config->obfuscator->cipher;
        $key = File::read($this->frameworkBase->parsePath((string) $this->config->obfuscator->keyFile));
        $key = $this->base64UrlDecode($key);
        $vec = File::read($this->frameworkBase->parsePath((string) $this->config->obfuscator->vectorFile));
        $vec = $this->base64UrlDecode($vec);
        // decrypt
        $dec = $this->base64UrlDecode($data);
        $hmc = substr($dec, 0, 32);
        $raw = substr($dec, 32);
        $txt = openssl_decrypt($raw, $cip, $key, OPENSSL_RAW_DATA, $vec);
        $ver = hash_equals($hmc, hash_hmac('sha256', $raw, $key, true));
        // verify decrypted data
        $ex = explode('.', $txt);
        $tM = (string) $ex[0] === (string) $table;
        $uM = (string) $ex[2] === (string) $userId;

        if ($ver && $tM && $uM) {
            return (string) $ex[1];
        }
        return (string) $data;
    }

    /**
     * Encodes string to base64url
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodes base64url string
     *
     * @param string $data
     * @return string
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(
            str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)
        );
    }

    /**
     * Creates key file
     *
     * @codeCoverageIgnore
     */
    private function createKeyFile(): void
    {
        $key = $this->frameworkBase->parsePath((string) $this->config->obfuscator->keyFile);
        if (!File::exists($key)) {
            File::write($key, $this->base64UrlEncode(openssl_random_pseudo_bytes(64)));
        }
    }

    /**
     * Creates initialization vector file
     *
     * @codeCoverageIgnore
     */
    private function createVectorFile(): void
    {
        $vec = $this->frameworkBase->parsePath((string) $this->config->obfuscator->vectorFile);
        $cip = (string) $this->config->obfuscator->cipher;
        if (!File::exists($vec)) {
            File::write($vec, $this->base64UrlEncode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($cip))));
        }
    }
}
