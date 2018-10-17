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

use MS\LightFramework\Base;
use MS\LightFramework\Config\AbstractConfig;
use MS\LightFramework\Config\Factory;
use MS\LightFramework\Filesystem\File;
use MS\LightFramework\Filesystem\FileName;
use MS\RestServer\Server\Auth\AbstractAuthProvider;
use MS\RestServer\Server\Controllers\AbstractController;
use MS\RestServer\Server\MapBuilder;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Shared\Headers;
use MS\Json\Utils\Utils;

/**
 * @codeCoverageIgnore
 */
class Server
{
    /**
     * @var Base
     */
    private $base;
    /**
     * @var AbstractConfig
     */
    private $config;
    /**
     * @var Headers
     */
    private $headers;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var string
     */
    private $requestMethod = 'GET';
    /**
     * @var string
     */
    private $requestUri = '/';
    /**
     * @var string
     */
    private $apiBrowserUri = '/api-browser';
    /**
     * @var string
     */
    private $definitionsDir = '%DOCUMENT_ROOT%/definitions/';
    /**
     * @var array
     */
    private $controllers = [];
    /**
     * @var array
     */
    private $defaultHeaders = [
        ['name' => 'Content-Type', 'value' => 'application/json; charset=utf-8'],
        ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
    ];
    /**
     * @var array
     */
    private $statuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        110 => 'Connection Timed Out',
        111 => 'Connection refused',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        310 => 'Too many redirects',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I’m a teapot',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    /**
     * Server constructor
     */
    public function __construct()
    {
        $this->base = Base::getInstance();
        $this->config = Factory::read($_ENV['CONFIG_FILE_SERVER']);
        $this->headers = new Headers($this->defaultHeaders);
        $this->utils = new Utils();

        $this->apiBrowserUri = $this->config->apiBrowserUri;
        $this->controllers = $this->config->controllers;
        $this->definitionsDir = $this->base->parsePath($this->config->definitionsDirectory);

        try {
            $this->request = new Request();
            $this->requestMethod = $this->request->getRequestHttpMethod();
            $this->requestUri = $this->request->getRequestUri();
        } catch (ResponseException $e) {
            $this->getResponseException($e);
        }
    }

    /**
     * Sets default auth provider
     *
     * @param AbstractAuthProvider $authProvider
     */
    public function setDefaultAuthProvider(AbstractAuthProvider $authProvider): void
    {
        $this->request->setDefaultAuthProvider($authProvider);
    }

    /**
     * Handles request
     *
     * @return null|string
     */
    public function getResponse(): ?string
    {
        if ($this->requestMethod === 'OPTIONS') {
            $this->sendHeaders();
            return null;
        }
        if (preg_match('|^'.$this->apiBrowserUri.'/?$|i', $this->requestUri)) {
            $browser = new Browser();
            return $browser->display();
        }

        try {
            $response = $this->getResponseBody();
        } catch (ResponseException $e) {
            $response =$this->getResponseException($e);
        }

        $this->sendHeaders();
        return $response;
    }

    /**
     * Sends response headers
     */
    private function sendHeaders(): void
    {
        foreach ($this->headers->getHeaders() as $headerName => $headerValue) {
            if (is_numeric($headerName)) {
                header($headerValue, true, $headerName);
            }
            if (!is_numeric($headerName)) {
                header(sprintf('%s: %s', $headerName, $headerValue));
            }
        }
    }

    /**
     * Returns response status
     *
     * @param int $code
     * @return string
     */
    private function getStatus(int $code = 0): string
    {
        return isset($this->statuses[$code]) ? $this->statuses[$code] : '';
    }


    /**
     * @return AbstractController
     * @throws ResponseException
     */
    private function getController(): AbstractController
    {
        foreach ($this->controllers as $controller) {
            if (stripos($this->requestUri, $controller->uri) === 0) {
                $controllerClass = (string)$controller->class;
                $mapFile = FileName::getSafe($controller->uri);
                $mapFilePath = \sprintf('%s%s.json', $this->definitionsDir, $mapFile);

                if (!class_exists($controllerClass)) {
                    throw new ResponseException(500, null, ['message' => 'Controller class not found']);
                }
                if (!File::exists($mapFilePath)) {
                    $mapBuilder = new MapBuilder();
                    $mapBuilder->build();
                }
                $this->request->setMapFilePath($mapFilePath);

                return new $controllerClass($this->request);
            }
        }
        throw new ResponseException(404, null, ['message' => \sprintf('No controller matching uri: \'/%s\'', $this->requestUri)]);
    }

    /**
     * Prints response body
     *
     * @throws ResponseException
     */
    private function getResponseBody()
    {
        $controller = $this->getController();
        $response = $controller->getResponse();

        $code = $response->getCode();
        $status = $this->getStatus($code);
        $body = $response->getBody();

        $this->headers->addHeaders(['name' => $code, 'value' => sprintf('HTTP/1.1 %s %s', $code, $status)]);

        if (is_array($body) || is_object($body)) {
            try {
                $body = $this->utils->encode($body);
            } catch (\Exception $e) {
                $this->headers->addHeaders(['name' => 500, 'value' => 'HTTP/1.1 500 Internal Server Error']);
                $body = '{"message" => "Error while encoding response"}';
            }
        }

        return $body;
    }

    /**
     * Prints response exception
     *
     * @param ResponseException $exception
     * @return string
     */
    private function getResponseException(ResponseException $exception): string
    {
        $code = $exception->getCode();
        $status = $this->getStatus($code);
        $message = $exception->getMessage();
        $errors = $exception->getErrors();

        $this->headers->addHeaders(['name' => $code, 'value' => sprintf('HTTP/1.1 %s %s', $code, $status)]);

        $body = [
            'message' => $message !== '' ? sprintf('%s: %s', $status, $message) : $status
        ];
        if (\count($errors) > 0) {
            $body['errors'] = $errors;
        }
        try {
            $body = $this->utils->encode($body);
        } catch (\Exception $exception) {
            $this->headers->addHeaders(['name' => 500, 'value' => 'HTTP/1.1 500 Internal Server Error']);
            $body = '{"message" => "Error while encoding response exception"}';
        }

        return $body;
    }
}
