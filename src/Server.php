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

use MS\RestServer\Server\Auth\AbstractAuthProvider;
use MS\RestServer\Server\Controllers\AbstractController;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Response;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Shared\Headers;
use MS\Json\Utils\Utils;

/**
 * @codeCoverageIgnore
 */
class Server
{
    /**
     * @var Utils
     */
    private $utils = null;
    /**
     * @var array
     */
    private $request = [];
    /**
     * @var Headers
     */
    private $headers;
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
    private $controllers = [];
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
        $this->utils = new Utils();
        $this->headers = new Headers($this->defaultHeaders);

        try {
            $this->request = new Request();
        } catch (ResponseException $e) {
            $this->printResponseException($e);
        }
    }

    /**
     * Adds server controller
     *
     * @param string $controllerName
     * @param string $controllerClass
     */
    public function addController(string $controllerName = null, string $controllerClass = null): void
    {
        if ($controllerName !== null && $controllerClass !== null) {
            $this->controllers[$controllerName] = $controllerClass;
            $this->request->setControllersMap($this->controllers);
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
     */
    public function handleRequest(): void
    {
        $requestHttpMethod = $this->request->getRequestHttpMethod();

        if ($requestHttpMethod === 'OPTIONS') {
            $this->sendHeaders();
            return;
        }

        try {
            echo $this->printResponseBody($this->getResponse());
        } catch (ResponseException $e) {
            echo $this->printResponseException($e);
        }
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
     * Returns server response
     *
     * @return Response
     * @throws ResponseException
     */
    private function getResponse(): Response
    {
        $controllerName = $this->request->getRequestControllerName();

        if (!array_key_exists($controllerName, $this->controllers)) {
            throw new ResponseException(404, null, ['message' => sprintf('No controller for \'/%s\' has been registered', $controllerName)]);
        }
        if (!class_exists($this->controllers[$controllerName])) {
            throw new ResponseException(500, null, ['message' => 'Controller class not found']);
        }

        /**
         * @var AbstractController $controller
         */
        $controller = new $this->controllers[$controllerName]($this->request);
        return $controller->getResponse();
    }

    /**
     * Returns response status
     *
     * @param int $code
     * @return string
     */
    public function getStatus(int $code = 0): string
    {
        return isset($this->statuses[$code]) ? $this->statuses[$code] : '';
    }

    /**
     * Prints response body
     *
     * @param Response $response
     * @return string
     */
    private function printResponseBody(Response $response): string
    {
        $code = $response->getCode();
        $status = $this->getStatus($code);
        $body = $response->getBody();

        $this->headers->addHeaders(['name' => $code, 'value' => sprintf('HTTP/1.1 %s %s', $code, $status)]);

        if (!is_array($body) && !is_object($body)) {
            $body = (string)$body;
        }
        if (is_array($body) || is_object($body)) {
            try {
                $body = $this->utils->encode($body);
            } catch (\Exception $e) {
                $this->headers->addHeaders(['name' => 500, 'value' => 'HTTP/1.1 500 Internal Server Error']);
                $body = '{"message" => "Error while encoding response"}';
            }
        }

        $this->sendHeaders();
        return $body;
    }

    /**
     * Prints response exception
     *
     * @param ResponseException $exception
     * @return string
     */
    private function printResponseException(ResponseException $exception): string
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

        $this->sendHeaders();
        return $body;
    }
}
