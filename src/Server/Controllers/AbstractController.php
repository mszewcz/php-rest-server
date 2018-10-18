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
    protected $request;
    /**
     * @var bool
     */
    protected $authorizationResult = false;
    /**
     * @var array
     */
    protected $authorizedUserData = [];

    /**
     * AbstractController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->base = Base::getInstance();
        $this->request = $request;
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
        $endpointInput = [];
        $endpointMethodName = null;

        foreach ($endpointMap as $endpointData) {
            $httpMethodMatched = ($endpointData['endpointHttpMethod'] === $this->request->getRequestHttpMethod());
            $uriMatched = \preg_match($endpointData['endpointUriPattern'], $requestUri);

            if ($httpMethodMatched && $uriMatched) {
                $endpointAuthProvider = $endpointData['endpointAuthProvider'];
                $endpointInput = $endpointData['endpointInput'];
                $endpointMethodName = $endpointData['endpointMethodName'];

                $this->request->setRequestPathParams($endpointData);
                $this->request->setRequestQueryParams($endpointData);
            }
        }

        // Authorize user
        $this->authorizeUser($endpointAuthProvider);
        if ($this->authorizationResult === false) {
            throw new ResponseException(
                401,
                null,
                ['message' => 'User authorization required']
            );
        }

        // Validate input
        $inputErrors = $this->validateInput($endpointInput);
        if (\count($inputErrors) > 0) {
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
            $this->authorizedUserData = [];
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
            $this->authorizedUserData = $authProvider->getUserData();
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
        $this->authorizedUserData = $authProviderClass->getUserData();
    }

    /**
     * Validates input
     *
     * @param array $endpointInput
     * @return array
     */
    private function validateInput(array $endpointInput): array
    {
        $inputErrors = [];

        // Validate path params
        if (\array_key_exists('path', $endpointInput)) {
            $validator = new InputPathValidator($this->request, $endpointInput['path']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate query params
        if (\array_key_exists('query', $endpointInput)) {
            $validator = new InputQueryValidator($this->request, $endpointInput['query']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate request body
        if (\array_key_exists('body', $endpointInput)) {
            $validator = new InputBodyValidator($this->request, $endpointInput);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        return $inputErrors;
    }
}
