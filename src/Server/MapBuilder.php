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

use MS\Json\Utils\Utils;
use MS\LightFramework\Base;
use MS\LightFramework\Filesystem\File;
use MS\RestServer\Server\Exceptions\MapBuilderException;
use MS\RestServer\Server\Exceptions\ResponseException;


class MapBuilder
{
    /**
     * @var Base
     */
    private $frameworkBase;
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var array
     */
    private $controllers = [];

    /**
     * MapBuilder constructor.
     */
    public function __construct()
    {
        $this->frameworkBase = Base::getInstance();
        $this->utils = new Utils();
    }

    /**
     * Adds controller
     *
     * @param string|null $controllerName
     * @param string|null $controllerClass
     */
    public function addController(string $controllerName = null, string $controllerClass = null): void
    {
        if ($controllerName !== null && $controllerClass !== null) {
            $this->controllers[$controllerName] = $controllerClass;
        }
    }

    /**
     * Builds all controllers' maps
     *
     * @return array
     * @throws ResponseException
     */
    public function buildMaps(): array
    {
        $result = [];
        foreach ($this->controllers as $controllerName => $controllerClass) {
            $result[] = $this->buildControllerMap($controllerName, $controllerClass);
        }
        return $result;
    }

    /**
     * Builds single controller's map
     *
     * @param string $controllerName
     * @param string $controllerClass
     * @return array
     * @throws ResponseException
     */
    private function buildControllerMap(string $controllerName = '', string $controllerClass = ''): array
    {
        $endpointMap = [];

        try {
            $classReflection = new \ReflectionClass($controllerClass);
            $methods = $classReflection->getMethods(\ReflectionMethod::IS_PROTECTED);

            foreach ($methods as $method) {
                $docComment = $method->getDocComment();

                $endpointMethodName = $method->getName();
                $endpointDesc = $this->getEndpointDescription($docComment);
                $endpointHttpMethod = $this->getEndpointHttpMethod($docComment);
                $endpointUri = $this->getEndpointUri($docComment);
                $endpointUriPattern = $this->getEndpointUriPattern($endpointUri);
                $endpointInput = $this->getEndpointInput($docComment);
                $endpointOutput = $this->getEndpointOutput($docComment);
                $endpointAuthProvider = $this->getEndpointAuthProvider($docComment);

                // @codeCoverageIgnoreStart
                if ($endpointHttpMethod === null) {
                    $message = sprintf('[%s::%s()] @api:method not found or invalid', $controllerClass, $endpointMethodName);
                    throw new MapBuilderException($message);
                }
                if ($endpointUri === null) {
                    $message = sprintf('[%s::%s()] @api:uri not found or invalid', $controllerClass, $endpointMethodName);
                    throw new MapBuilderException($message);
                }
                foreach ($endpointMap as $existingEndpoint) {
                    if ($existingEndpoint['endpointHttpMethod'] === $endpointHttpMethod && $existingEndpoint['endpointUri'] === $endpointUri) {
                        $message = \sprintf('[%s::%s()] method for \'%s %s\' already assigned.', $controllerClass, $endpointMethodName, \strtoupper($endpointHttpMethod), $endpointUri);
                        throw new MapBuilderException($message);
                    }
                }
                // @codeCoverageIgnoreEnd

                $endpointMap[] = [
                    'endpointMethodName'   => $endpointMethodName,
                    'endpointDesc'         => $endpointDesc,
                    'endpointHttpMethod'   => $endpointHttpMethod,
                    'endpointUri'          => $endpointUri,
                    'endpointUriPattern'   => $endpointUriPattern,
                    'endpointInput'        => $endpointInput,
                    'endpointOutput'       => $endpointOutput,
                    'endpointAuthProvider' => $endpointAuthProvider
                ];
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            throw new ResponseException(500, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

        try {
            $endpointMapEncoded = $this->utils->encode($endpointMap);
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            throw new ResponseException(500, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

        File::write(sprintf('%s/mapFiles/%s.json', $this->frameworkBase->parsePath('%DOCUMENT_ROOT%'), $controllerName), $endpointMapEncoded);
        return $endpointMap;
    }

    /**
     * Returns endpoint description
     *
     * @param string $docComment
     * @return string
     */
    private function getEndpointDescription(string $docComment): string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:desc (.*?)[\r\n]?$/mi', $docComment, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * Returns endpoint http method
     *
     * @param string $docComment
     * @return null|string
     */
    private function getEndpointHttpMethod(string $docComment): ?string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:method[^\/]+(get|post|put|delete)[\r\n]?$/mi', $docComment, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns endpoint uri
     *
     * @param string $docComment
     * @return null|string
     */
    private function getEndpointUri(string $docComment): ?string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:uri[^\/]+(.*?)[\r\n]?$/mi', $docComment, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns endpoint uri pattern
     *
     * @param null|string $endpointUri
     * @return null|string
     */
    private function getEndpointUriPattern(?string $endpointUri): ?string
    {
        $endpointUriPattern = \preg_replace('/\/({[^}]+})/', '/([^/]+)', \sprintf('%s', $endpointUri));
        $endpointUriPattern = \preg_replace('/=({[^}]+})/', '=([^&]+)', $endpointUriPattern);
        $endpointUriPattern = \str_replace('?', '\\?', $endpointUriPattern);
        return \sprintf('|^%s$|i', $endpointUriPattern);
    }

    /**
     * Returns endpoint input
     *
     * @param null|string $docComment
     * @return array
     */
    private function getEndpointInput(?string $docComment): array
    {
        $endpointInput = [];

        \preg_match_all('/^[^\*]+\*[^@]+@api:input:(path|query|body) (.*?)[\r\n]?$/mi', $docComment, $matches);
        if (\count($matches) > 0) {
            foreach ($matches[1] as $key => $value) {
                $param = explode(':', $matches[2][$key]);
                $endpointInput[$value][] = ['paramName' => $param[0], 'paramType' => $param[1], 'paramRequired' => $param[2] === 'required'];
            }
        }

        return $endpointInput;
    }

    /**
     * Returns endpoint output
     *
     * @param null|string $docComment
     * @return null|string
     */
    private function getEndpointOutput(?string $docComment): ?string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:output (.*?)[\r\n]?$/mi', $docComment, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns endpoint auth provider
     *
     * @param null|string $docComment
     * @return null|string
     */
    private function getEndpointAuthProvider(?string $docComment): ?string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:auth (.*?)[\r\n]?$/mi', $docComment, $matches);
        return isset($matches[1]) ? $matches[1] : 'none';
    }
}
