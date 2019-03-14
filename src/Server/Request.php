<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server;

use MS\RestServer\Base;
use MS\RestServer\Server\Auth\AbstractAuthProvider;


class Request
{
    /**
     * @var Base
     */
    private $base;
    /**
     * @var string
     */
    private $pathInfo = '';
    /**
     * @var array
     */
    private $pathArray = [];
    /**
     * @var string
     */
    private $requestMethod = 'GET';
    /**
     * @var null
     */
    private $requestUri = null;
    /**
     * @var null
     */
    private $requestController = null;
    /**
     * @var null
     */
    private $requestAuthProvider = null;
    /**
     * @var array
     */
    private $requestParamsQuery = [];
    /**
     * @var array
     */
    private $requestParamsPath = [];
    /**
     * @var null
     */
    private $requestBody = null;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->base = Base::getInstance();
        $this->setRequestMethod();
        $this->setRequestUri();
        $this->setRequestController();
        $this->setRequestBody();
    }

    /**
     * Returns request http method
     *
     * @return string
     */
    public function getRequestHttpMethod(): string
    {
        return $this->requestMethod ? strtolower($this->requestMethod) : 'get';
    }

    /**
     * Returns request uri
     *
     * @return string|null
     */
    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    /**
     * Returns request controller name
     *
     * @return string|null
     */
    public function getRequestControllerName(): ?string
    {
        return $this->requestController;
    }

    /**
     * Returns auth provider class
     *
     * @return AbstractAuthProvider|null
     */
    public function getDefaultAuthProvider(): ?AbstractAuthProvider
    {
        return $this->requestAuthProvider;
    }

    /**
     * Returns request path params
     *
     * @return array
     */
    public function getRequestPathParams(): array
    {
        return $this->requestParamsPath;
    }

    /**
     * Returns request query params
     *
     * @return array
     */
    public function getRequestQueryParams(): array
    {
        return $this->requestParamsQuery;
    }

    /**
     * Returns request body
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Sets request's auth provider
     *
     * @param AbstractAuthProvider $authProvider
     */
    public function setDefaultAuthProvider(AbstractAuthProvider $authProvider): void
    {
        $this->requestAuthProvider = $authProvider;
    }

    /**
     * Set request path params
     *
     * @param array $endpointData
     * @codeCoverageIgnore
     */
    public function setRequestPathParams(array $endpointData): void
    {
        preg_match_all('|([^/]+)/?|', explode('?', $endpointData['endpointUri'])[0], $matches);

        if (isset($matches[1])) {
            $matchesCount = count($matches[1]);

            for ($i = 0; $i < $matchesCount; $i++) {
                $hasOpeningBracket = strpos($matches[1][$i], '{') !== false;
                $hasClosingBracket = strpos($matches[1][$i], '}') !== false;

                if ($hasOpeningBracket && $hasClosingBracket) {
                    $paramName = str_replace(['{', '}'], '', $matches[1][$i]);
                    $paramValue = $this->pathArray[$i];
                    $this->requestParamsPath[$paramName] = $this->parseValue($paramValue);
                }
            }
        }
    }

    /**
     * Set request query params
     *
     * @param array $endpointData
     * @codeCoverageIgnore
     */
    public function setRequestQueryParams(array $endpointData): void
    {
        $uriExploded = explode('?', $endpointData['endpointUri']);

        if (isset($uriExploded[1])) {
            preg_match_all('|({[^}]+})?&?([^=]+)=|', $uriExploded[1], $matches);

            if (isset($matches[2])) {
                foreach ($matches[2] as $match) {
                    $paramName = $match;
                    $paramValue = filter_input(INPUT_GET, $paramName, FILTER_DEFAULT);
                    $this->requestParamsQuery[$paramName] = $this->parseValue($paramValue);
                }
            }
        }

    }

    /**
     * Sets request method
     */
    private function setRequestMethod(): void
    {
        $this->requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_DEFAULT);
    }

    /**
     * Sets request uri
     *
     * @codeCoverageIgnore
     */
    private function setRequestUri(): void
    {
        $scriptName = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_DEFAULT);
        $requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
        $phpSelf = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_DEFAULT);

        $scriptDir = explode('/', $scriptName);
        array_pop($scriptDir);
        $scriptDir = implode('/', $scriptDir);

        $this->pathInfo = filter_has_var(INPUT_SERVER, 'PATH_INFO')
            ? filter_input(INPUT_SERVER, 'PATH_INFO', FILTER_DEFAULT)
            : str_replace($scriptName, '', $phpSelf);

        $this->pathArray = explode('/', $this->pathInfo);
        array_shift($this->pathArray);

        $this->requestUri = str_replace($scriptDir, '', $requestUri);
    }

    /**
     * Sets request controller
     */
    private function setRequestController(): void
    {
        $pathArray = $this->pathArray;
        $this->requestController = array_shift($pathArray);
    }

    /**
     * Sets request body
     *
     * @codeCoverageIgnore
     */
    private function setRequestBody(): void
    {
        switch ($this->requestMethod) {
            case 'GET':
                if (\filter_has_var(INPUT_GET, 'body')) {
                    $requestBody = filter_input(INPUT_GET, 'body', FILTER_DEFAULT);
                    $this->requestBody = $this->parseValue($requestBody);
                }
                break;
            default:
                $phpInput = \file_get_contents('php://input');
                if ($phpInput !== false && $phpInput !== '') {
                    $this->requestBody = $this->parseValue($phpInput);
                }
                break;
        }
    }

    /**
     * Parses value
     *
     * @param $paramValue
     * @return array|bool|float|int|null|\stdClass|string
     * @codeCoverageIgnore
     */
    private function parseValue($paramValue)
    {
        if (is_null($paramValue) || $paramValue === '' || $paramValue === 'null') {
            return null;
        }
        if (preg_match('/^\d+$/', $paramValue)) {
            return (int)$paramValue;
        }
        if (preg_match('/^\d+(\.\d+)?$/', $paramValue)) {
            return (float)$paramValue;
        }
        if (preg_match('/^(false|true)?$/i', $paramValue)) {
            return (bool)$paramValue;
        }
        if (preg_match('/^\[|{/', $paramValue) && preg_match('/\]|}$/', $paramValue)) {
            return $this->base->decode($paramValue);
        }

        return (string)$paramValue;
    }
}
