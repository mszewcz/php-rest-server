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
use MS\RestServer\Server\Auth\AuthorizationResult;
use MS\RestServer\Server\Auth\AuthorizedUser;
use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;
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
     * @var LocalizationService
     */
    private $localizationService;
    /**
     * @var Request
     */
    private $request;
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
        $this->localizationService = LocalizationService::getInstance();
        $this->request = $request;
        $this->authorizedUser = new AuthorizedUser();
    }

    /**
     * Invokes controller method and returns response
     *
     * @param string $endpointMethodName
     * @return Response
     * @throws ResponseException
     */
    public function invoke(string $endpointMethodName): Response
    {
        $requestUri = $this->request->getRequestUri();
        $endpointMap = $this->getControllerMap();
        $endpointAuthProvider = $endpointMap[$endpointMethodName]['endpointAuthProvider'];
        $endpointParams = $endpointMap[$endpointMethodName]['endpointParams'];

        $this->request->setRequestPathParams($endpointMap[$endpointMethodName]);
        $this->request->setRequestQueryParams($endpointMap[$endpointMethodName]);

        $this->requestPathParams = $this->request->getRequestPathParams();
        $this->requestQueryParams = $this->request->getRequestQueryParams();
        $this->requestBody = $this->request->getRequestBody();

        // Authorize user
        $authorizationResult = $this->authorizeUser($endpointAuthProvider);
        if ($authorizationResult->getResult() === false) {
            $exception = new ResponseException(401);
            $exception->addError($authorizationResult->getError());
            throw $exception;
        }
        $this->authorizedUser = $authorizationResult->getUser();

        // Validate input
        $inputErrors = $this->validateInput($endpointParams);
        if (count($inputErrors) > 0) {
            $exception = new ResponseException(400);
            foreach ($inputErrors as $error) {
                $exception->addError($error);
            }
            throw $exception;
        }

        // Invoke endpoint method
        if ($endpointMethodName === null) {
            $errorC = ServerErrors::NO_METHOD_MATCHING_URI_CODE;
            $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

            $exception = new ResponseException(404);
            $exception->addError(new ErrorModel($errorC, sprintf($errorM, $requestUri)));
            throw $exception;
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
     * @return array|bool|float|int|object|string|null
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
     * @return array|bool|float|int|object|string|null
     */
    public function getRequestQueryParam(string $paramName = '')
    {
        return isset($this->requestQueryParams[$paramName]) ? $this->requestQueryParams[$paramName] : null;
    }

    /**
     * Returns request body
     *
     * @return array|bool|float|int|object|string|null
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
            $errorC = ServerErrors::METHOD_MAP_FILE_MISSING_CODE;
            $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

            $exception = new ResponseException(500);
            $exception->addError(new ErrorModel($errorC, sprintf($errorM, $requestUri)));
            throw $exception;
        }

        $map = $this->base->decodeAsArray($this->base->fileRead($mapFilePath));
        if ($map === false) {
            $errorC = ServerErrors::METHOD_MAP_FILE_DECODING_ERROR_CODE;
            $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

            $exception = new ResponseException(500);
            $exception->addError(new ErrorModel($errorC, sprintf($errorM, $requestUri)));
            throw $exception;
        }
        return $map;
    }

    /**
     * Checks authorization status
     *
     * @param string $authProvider
     * @return AuthorizationResult
     * @throws ResponseException
     */
    private function authorizeUser(string $authProvider): AuthorizationResult
    {
        $noAuthProvider = ['false', 'no', 'none'];
        $defaultAuthProvider = ['true', 'yes', 'default'];

        // No Auth Provider
        if (in_array($authProvider, $noAuthProvider)) {
            return new AuthorizationResult(true, null, new AuthorizedUser());
        }

        // Default Auth Provider
        if (in_array($authProvider, $defaultAuthProvider)) {
            $authProvider = $this->request->getDefaultAuthProvider();
            if ($authProvider === null) {
                $errorC = ServerErrors::DEFAULT_AUTH_PROVIDER_NOT_SET_CODE;
                $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

                $exception = new ResponseException(500);
                $exception->addError(new ErrorModel($errorC, $errorM));
                throw $exception;
            }
            return $authProvider->authorize();
        }

        // Custom Auth Provider
        if (!\class_exists($authProvider)) {
            $errorC = ServerErrors::AUTH_PROVIDER_CLASS_DOES_NOT_EXIST_CODE;
            $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

            $exception = new ResponseException(500);
            $exception->addError(new ErrorModel($errorC, sprintf($errorM, $authProvider)));
            throw $exception;
        }
        $authProviderClass = new $authProvider;
        if (!($authProviderClass instanceof AbstractAuthProvider)) {
            $errorC = ServerErrors::AUTH_PROVIDER_CLASS_INSTANCE_ERROR_CODE;
            $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));

            $exception = new ResponseException(500);
            $exception->addError(new ErrorModel($errorC, sprintf($errorM, $authProvider)));
            throw $exception;
        }
        return $authProviderClass->authorize();
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
