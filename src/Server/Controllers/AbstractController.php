<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Controllers;

use MS\RestServer\Base;
use MS\RestServer\Server\Auth\AbstractAuthProvider;
use MS\RestServer\Server\Auth\AbstractUser;
use MS\RestServer\Server\Auth\AuthorizedUser;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Server\Validators\InputQueryValidator;
use MS\RestServer\Server\Validators\InputPathValidator;
use MS\RestServer\Server\Validators\InputBodyValidator;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Response;


/**
 * @codeCoverageIgnore
 */
class AbstractController
{
    /**
     * @var Base
     */
    private $base;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var bool
     */
    protected $authorizationResult = false;
    /**
     * @var AbstractUser
     */
    protected $authorizedUser;
    /**
     * @var array
     */
    private $requestPathParams = [];
    /**
     * @var array
     */
    private $requestQueryParams = [];
    /**
     * @var
     */
    private $requestBody = null;

    /**
     * AbstractController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->base = Base::getInstance();
        $this->request = $request;
        $this->authorizedUser = new AuthorizedUser();
    }

    /**
     * Returns response
     *
     * @return Response
     * @throws ResponseException
     */
    public function getResponse(): Response
    {
        $requestUri = $this->request->getRequestUri();
        $endpointMap = $this->getControllerMap();
        $endpointAuthProvider = 'none';
        $endpointParams = [];
        $endpointMethodName = null;

        foreach ($endpointMap as $endpointData) {
            $httpMethodMatched = ($endpointData['endpointHttpMethod'] === $this->request->getRequestHttpMethod());
            $uriMatched = preg_match($endpointData['endpointUriPattern'], $requestUri);

            if ($httpMethodMatched && $uriMatched) {
                $endpointAuthProvider = $endpointData['endpointAuthProvider'];
                $endpointParams = $endpointData['endpointParams'];
                $endpointMethodName = $endpointData['endpointMethodName'];

                $this->request->setRequestPathParams($endpointData);
                $this->request->setRequestQueryParams($endpointData);

                $this->requestPathParams = $this->request->getRequestPathParams();
                $this->requestQueryParams = $this->request->getRequestQueryParams();
                $this->requestBody = $this->request->getRequestBody();
            }
        }

        // Authorize user
        $this->authorizeUser($endpointAuthProvider);
        if ($this->authorizationResult === false) {
            throw new ResponseException(
                401,
                null,
                ['message' => 'Authorization required']
            );
        }

        // Validate input
        $inputErrors = $this->validateInput($endpointParams);
        if (count($inputErrors) > 0) {
            throw new ResponseException(
                400,
                null,
                $inputErrors
            );
        }

        // Invoke endpoint method
        if ($endpointMethodName === null) {
            throw new ResponseException(
                404,
                null,
                ['message' => sprintf('No method matching uri: %s', $requestUri)]
            );
        }

        return \call_user_func([$this, $endpointMethodName]);
    }

    /**
     * Returns request path params
     *
     * @return array
     */
    public function getRequestPathParams()
    {
        return $this->requestPathParams;
    }

    /**
     * Returns request path param
     *
     * @param string $paramName
     * @return mixed|null
     */
    public function getRequestPathParam(string $paramName = '')
    {
        return isset($this->requestPathParams[$paramName]) ? $this->requestPathParams[$paramName] : null;
    }

    /**
     * Returns request query params
     *
     * @return array
     */
    public function getRequestQueryParams()
    {
        return $this->requestQueryParams;
    }

    /**
     * Returns request query param
     *
     * @param string $paramName
     * @return mixed|null
     */
    public function getRequestQueryParam(string $paramName = '')
    {
        return isset($this->requestQueryParams[$paramName]) ? $this->requestQueryParams[$paramName] : null;
    }

    /**
     * Returns request body
     *
     * @return mixed|null
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * @return array
     * @throws ResponseException
     */
    private function getControllerMap(): array
    {
        $requestUri = $this->request->getRequestUri();
        $mapFilePath = $this->base->getMapFilePath();
        // Load method map file for current controller
        if (!$this->base->fileExists($mapFilePath)) {
            throw new ResponseException(
                500,
                null,
                ['message' => sprintf('Method map file is missing for route: %s', $requestUri)]
            );
        }

        $map = $this->base->decodeAsArray($this->base->fileRead($mapFilePath));
        if ($map === false) {
            throw new ResponseException(
                500,
                null,
                ['message' => 'Method map file decoding error']
            );
        }
        return $map;
    }

    /**
     * Checks authorization status
     *
     * @param string $authProvider
     * @return void
     * @throws ResponseException
     */
    private function authorizeUser(string $authProvider): void
    {
        $noAuthProvider = ['false', 'no', 'none'];
        $defaultAuthProvider = ['true', 'yes', 'default'];

        // No Auth Provider
        if (in_array($authProvider, $noAuthProvider)) {
            $this->authorizationResult = true;
            $this->authorizedUser = new AuthorizedUser();
            return;
        }

        // Default Auth Provider
        if (in_array($authProvider, $defaultAuthProvider)) {
            $authProvider = $this->request->getDefaultAuthProvider();
            if ($authProvider === null) {
                throw new ResponseException(
                    500,
                    null,
                    ['message' => 'Default auth provider is not set']
                );
            }
            $this->authorizationResult = $authProvider->authorize();
            $this->authorizedUser = $authProvider->getUser();
            return;
        }

        // Custom Auth Provider
        if (!\class_exists($authProvider)) {
            throw new ResponseException(
                500,
                null,
                ['message' => sprintf('%s class does not exist', $authProvider)]
            );
        }
        $authProviderClass = new $authProvider;
        if (!($authProviderClass instanceof AbstractAuthProvider)) {
            throw new ResponseException(
                500,
                null,
                ['message' => sprintf('%s has to be an instance of AbstractAuthProvider', $authProvider)]
            );
        }
        $this->authorizationResult = $authProviderClass->authorize();
        $this->authorizedUser = $authProviderClass->getUser();
    }

    /**
     * Validates input
     *
     * @param array $endpointParams
     * @return array
     */
    private function validateInput(array $endpointParams): array
    {
        $inputErrors = [];

        // Validate path params
        if (\array_key_exists('path', $endpointParams)) {
            $validator = new InputPathValidator($this->request, $endpointParams['path']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate query params
        if (\array_key_exists('query', $endpointParams)) {
            $validator = new InputQueryValidator($this->request, $endpointParams['query']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate request body
        if (\array_key_exists('body', $endpointParams)) {
            $validator = new InputBodyValidator($this->request, $endpointParams);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        return $inputErrors;
    }
}
