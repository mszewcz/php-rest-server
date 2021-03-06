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
use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\MapBuilder;
use MS\RestServer\Server\Models\ErrorModel;
use MS\RestServer\Server\Request;
use MS\RestServer\Shared\Headers;

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
     * @var LocalizationService
     */
    private $localizationService;
    /**
     * @var Headers
     */
    private $headers;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $requestMethod = 'GET';
    /**
     * @var string
     */
    private $requestUri = '/';
    /**
     * @var array
     */
    private $defaultHeaders = [
        ['name' => 'Content-Type', 'value' => 'application/json; charset=utf-8'],
        ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
        ['name' => 'Access-Control-Allow-Headers', 'value' => 'Content-Type, Authorization'],
        ['name' => 'Access-Control-Allow-Methods', 'value' => 'DELETE, GET, POST, PUT, OPTIONS'],
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
        $this->localizationService = LocalizationService::getInstance();
        $this->headers = new Headers($this->defaultHeaders);
        $this->request = new Request();
        $this->requestMethod = $this->request->getRequestHttpMethod();
        $this->requestUri = $this->request->getRequestUri();
    }

    /**
     * @return Headers
     */
    public function headers(): Headers {
        return $this->headers;
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
    public function getResponse()
    {
        if ($this->requestMethod === 'options') {
            $this->sendHeaders();
            return null;
        }
        if ($this->requestUri === '/') {
            return '';
        }
        if (preg_match('|^' . $this->base->getApiBrowserUri() . '|i', $this->requestUri)) {
            $browser = new Browser();
            return $browser->display();
        }

        try {
            $response = $this->getResponseBody();
        } catch (ResponseException $e) {
            $response = $this->getResponseException($e);
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
     * @return array
     * @throws ResponseException
     */
    private function getControllerData(): array
    {
        $requestUri = $this->request->getRequestUri();
        $controllers = $this->base->getControllers();
        $definitionsDir = $this->base->getDefinitionsDir();
        $matchingControllers = [];

        foreach ($controllers as $matchingController) {
            foreach ($matchingController->endpoints as $endpoint) {
                $endpointUriPattern = preg_replace('|/{[^}]+}|', '/[^/]+', $endpoint->uri);
                $endpointUriPattern = str_replace('/', '\\/', $endpointUriPattern);

                if (preg_match('/^' . $endpointUriPattern . '(\/.+|\?.+)?$/i', $this->requestUri)) {
                    $controllerClass = (string) $endpoint->class;
                    $mapFilePath = sprintf('%s%s.json', $definitionsDir, (string) $endpoint->mapFile);

                    if (!\class_exists($controllerClass)) {
                        $errorC = ServerErrors::CONTROLLER_NOT_FOUND_CODE;
                        $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

                        $exception = new ResponseException(500);
                        $exception->addError(new ErrorModel($errorC, $errorM));
                        throw $exception;
                    }
                    if (!$this->base->fileExists($mapFilePath)) {
                        $mapBuilder = new MapBuilder();
                        $mapBuilder->build();
                    }
                    $this->base->setMapFilePath($mapFilePath);

                    $matchingControllers[] = [
                        'controllerClass' => $controllerClass,
                        'mapFilePath'     => $mapFilePath
                    ];
                }
            }
        }

        $requestHttpMethod = $this->request->getRequestHttpMethod();

        foreach ($matchingControllers as $matchingController) {
            $endpointMap = $this->base->decodeAsArray($this->base->fileRead($matchingController['mapFilePath']));

            foreach ($endpointMap as $endpointMethodName => $endpointMethodData) {
                $httpMethodMatched = ($endpointMethodData['endpointHttpMethod'] === $requestHttpMethod);
                $uriMatched = preg_match($endpointMethodData['endpointUriPattern'], $requestUri);

                if ($httpMethodMatched && $uriMatched) {
                    return [
                        'controller'  => new $matchingController['controllerClass']($this->request),
                        'methodName'  => $endpointMethodName,
                        'mapFilePath' => $matchingController['mapFilePath']
                    ];
                }
            }
        }

        $errorC = ServerErrors::NO_CONTROLLER_MATCHING_URI_CODE;
        $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

        $exception = new ResponseException(404);
        $exception->addError(new ErrorModel($errorC, sprintf($errorM, $this->requestUri)));
        throw $exception;
    }

    /**
     * Prints response body
     *
     * @throws ResponseException
     */
    private function getResponseBody()
    {
        $controllerData = $this->getControllerData();
        /**
         * @var $controller AbstractController
         */
        $controller = $controllerData['controller'];
        $mapFilePath = $controllerData['mapFilePath'];
        $methodName = $controllerData['methodName'];
        $response = $controller->invoke($mapFilePath, $methodName);

        $contentType = $response->getContentType();
        $encoding = $response->getEncoding();
        $code = $response->getCode();
        $status = $this->getStatus($code);
        $body = $response->getBody();

        $responseHeaders = [
            ['name' => 'Content-Type', 'value' => sprintf('%s; charset=%s', $contentType, $encoding)],
            ['name' => $code, 'value' => sprintf('HTTP/1.1 %s %s', $code, $status)]
        ];

        $headers = $response->headers()->getHeaders();
        foreach ($headers as $name => $value) {
            $responseHeaders[] = ['name' => $name, 'value' => $value];
        }

        $this->headers->addHeaders($responseHeaders);

        if (is_array($body) || is_object($body)) {
            $body = $this->base->encode($body);
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
        $errors = $exception->getErrors();

        $this->headers->addHeaders(['name' => $code, 'value' => sprintf('HTTP/1.1 %s %s', $code, $status)]);
        return $this->base->encode($errors);
    }
}
