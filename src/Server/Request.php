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
use MS\RestServer\Server\Exceptions\ResponseException;


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
     *
     * @throws ResponseException
     */
    public function __construct()
    {
        $this->base = Base::getInstance();
        $this->setRequestMethod();
        $this->setRequestUri();
        $this->setRequestController();
        $this->setQueryParams();
        $this->setRequestBody();
    }

    /**
     * Returns path variables array
     *
     * @return array
     */
    public function getPathArray(): array
    {
        return $this->pathArray;
    }

    /**
     * Returns request http method
     *
     * @return string
     */
    public function getRequestHttpMethod(): string
    {
        return $this->requestMethod ? \strtolower($this->requestMethod) : 'get';
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
     * Sets path param
     *
     * @param string $paramName
     * @param $paramValue
     */
    public function setPathParam(string $paramName, $paramValue): void
    {
        $this->requestParamsPath[$paramName] = $paramValue;
    }

    /**
     * Sets request method
     */
    private function setRequestMethod(): void
    {
        $this->requestMethod = \filter_input(\INPUT_SERVER, 'REQUEST_METHOD', \FILTER_DEFAULT);
    }

    /**
     * Sets request uri
     *
     * @codeCoverageIgnore
     */
    private function setRequestUri(): void
    {
        $scriptName = \filter_input(\INPUT_SERVER, 'SCRIPT_NAME', \FILTER_DEFAULT);
        $requestUri = \filter_input(\INPUT_SERVER, 'REQUEST_URI', \FILTER_DEFAULT);
        $phpSelf = \filter_input(\INPUT_SERVER, 'PHP_SELF', \FILTER_DEFAULT);

        $scriptDir = explode('/', $scriptName);
        array_pop($scriptDir);
        $scriptDir = implode('/', $scriptDir);

        $this->pathInfo = \filter_has_var(\INPUT_SERVER, 'PATH_INFO')
            ? filter_input(\INPUT_SERVER, 'PATH_INFO', \FILTER_DEFAULT)
            : \str_replace($scriptName, '', $phpSelf);

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
     * Sets request query params
     *
     * @codeCoverageIgnore
     */
    private function setQueryParams(): void
    {
        foreach ($_GET as $paramName => $paramValue) {
            if ($paramName !== 'body') {
                $paramValue = filter_input(\INPUT_GET, $paramName, \FILTER_DEFAULT);

                $this->requestParamsQuery[$paramName] = $paramValue;
            }
        }
    }

    /**
     * Sets request body
     *
     * @throws ResponseException
     * @codeCoverageIgnore
     */
    private function setRequestBody(): void
    {
        $requestBody = null;

        switch ($this->requestMethod) {
            case 'GET':
                if (filter_has_var(\INPUT_GET, 'body')) {
                    $this->requestBody = filter_input(\INPUT_GET, 'body', \FILTER_DEFAULT);
                }
                break;
            default:
                $phpInput = \file_get_contents('php://input');
                if ($phpInput !== false && $phpInput !== '') {
                    $this->requestBody = $phpInput;
                }
                break;
        }

        if ($this->requestBody !== null && preg_match('/^\[|{/', $this->requestBody)) {
            try {
                $this->requestBody = $this->base->decode((string)$this->requestBody);
            } catch (\Exception $e) {
                throw new ResponseException(400, null, ['message' => 'Request body parse error']);
            }
        }
    }
}
