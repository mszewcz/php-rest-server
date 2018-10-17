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
        $endpointMap = $this->getControllerMap();
        $endpointAuthProvider = 'none';
        $endpointInput = [];
        $endpointMethodName = null;

        foreach ($endpointMap as $endpointData) {
            $httpMethodMatched = ($endpointData['endpointHttpMethod'] === $this->request->getRequestHttpMethod());
            $uriMatched = \preg_match($endpointData['endpointUriPattern'], $this->request->getRequestUri());

            if ($httpMethodMatched && $uriMatched) {
                $endpointAuthProvider = $endpointData['endpointAuthProvider'];
                $endpointInput = $endpointData['endpointInput'];
                $endpointMethodName = $endpointData['endpointMethodName'];

                $this->setRequestPathParams($endpointData);
            }
        }

        // Handle authorization
        $isAuthorized = $this->isAuthorized($endpointAuthProvider);
        if ($isAuthorized === false) {
            throw new ResponseException(401, null, ['message' => 'User authorization required']);
        }

        // Validate input
        $inputErrors = $this->validateInput($endpointInput);
        if (count($inputErrors) > 0) {
            throw new ResponseException(400, null, $inputErrors);
        }

        // Invoke endpoint method
        if ($endpointMethodName === null) {
            throw new ResponseException(404, null, ['message' => $this->request->getRequestUri()]);
        }

        return call_user_func([$this, $endpointMethodName]);
    }

    /**
     * @return array
     * @throws ResponseException
     */
    private function getControllerMap(): array
    {
        $mapFilePath = $this->base->getMapFilePath();
        // Load method map file for current controller
        if (!$this->base->fileExists($mapFilePath)) {
            throw new ResponseException(500, sprintf('Method map file is missing for route: %s', $this->request->getRequestUri()));
        }
        try {
            return $this->base->decode($this->base->fileRead($mapFilePath));
        } catch (\Exception $e) {
            throw new ResponseException(500, null, ['message' => 'Method map file decoding error']);
        }
    }

    /**
     * Set request path params
     *
     * @param array $endpointData
     */
    private function setRequestPathParams(array $endpointData): void
    {
        preg_match_all('|([^/]+)/?|', explode('?', $endpointData['endpointUri'])[0], $matches);

        if (isset($matches[1])) {
            $matchesCount = count($matches[1]);

            for ($i = 0; $i < $matchesCount; $i++) {
                $isParam = strpos($matches[1][$i], '{') !== false && strpos($matches[1][$i], '}') !== false;

                if ($isParam) {
                    $paramName = str_replace(['{', '}'], '', $matches[1][$i]);
                    $this->request->setPathParam($paramName, $this->request->getPathArray()[$i]);
                }
            }
        }
    }

    /**
     * Checks authorization status
     *
     * @param string $authProvider
     * @return bool
     * @throws ResponseException
     */
    private function isAuthorized(string $authProvider): bool
    {
        switch ($authProvider) {
            case 'false':
            case 'no':
            case 'none':
                $isAuthorized = true;
                break;
            case 'true':
            case 'yes':
            case 'default':
                $authProvider = $this->request->getDefaultAuthProvider();
                if ($authProvider === null) {
                    throw new ResponseException(500, null, ['message' => 'Default auth provider is not set']);
                }
                $isAuthorized = $authProvider->isAuthorized();
                break;
            default:
                if (!class_exists($authProvider)) {
                    throw new ResponseException(500, null, ['message' => sprintf('%s class does not exist', $authProvider)]);
                }
                $authProviderClass = new $authProvider;
                if (!($authProviderClass instanceof AbstractAuthProvider)) {
                    throw new ResponseException(500, null, ['message' => sprintf('%s has to be an instance of AbstractAuthProvider', $authProvider)]);
                }
                $isAuthorized = $authProviderClass->isAuthorized();
                break;
        }

        return $isAuthorized;
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
        if (array_key_exists('url', $endpointInput)) {
            $validator = new InputPathValidator($this->request, $endpointInput['path']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate query params
        if (array_key_exists('get', $endpointInput)) {
            $validator = new InputQueryValidator($this->request, $endpointInput['query']);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        // Validate request body
        if (array_key_exists('body', $endpointInput)) {
            $validator = new InputBodyValidator($this->request, $endpointInput);
            $errors = $validator->validate();
            foreach ($errors as $k => $v) {
                $inputErrors[$k] = $v;
            }
        }

        return $inputErrors;
    }
}
