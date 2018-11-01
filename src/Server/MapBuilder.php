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

use MS\LightFramework\Config\AbstractConfig;
use MS\RestServer\Base;
use MS\RestServer\Server\Exceptions\MapBuilderException;
use MS\RestServer\Server\Exceptions\ResponseException;


class MapBuilder
{
    /**
     * @var Base
     */
    private $base;

    /**
     * MapBuilder constructor
     */
    public function __construct()
    {
        $this->base = Base::getInstance();
    }

    /**
     * Builds all controllers' maps
     *
     * @return array
     * @throws ResponseException
     */
    public function build(): array
    {
        $controllers = $this->base->getControllers();

        $result = [];
        foreach ($controllers as $controller) {
            foreach ($controller->endpoints as $endpoint) {
                $result[] = $this->buildControllerMap($endpoint);
            }
        }
        return $result;
    }

    /**
     * Builds single controller's map
     *
     * @param AbstractConfig $endpoint
     * @return array
     * @throws ResponseException
     */
    private function buildControllerMap(AbstractConfig $endpoint): array
    {
        $definitionsDir = $this->base->getDefinitionsDir();
        $endpointMap = [];

        try {
            $controllerClass = (string) $endpoint->class;
            $mapFilePath = sprintf('%s%s.json', $definitionsDir, (string) $endpoint->mapFile);

            $classReflection = new \ReflectionClass($controllerClass);
            $methods = $classReflection->getMethods(\ReflectionMethod::IS_PROTECTED);

            foreach ($methods as $method) {
                $docComment = $method->getDocComment();

                $endpointMethodName = $method->getName();
                $endpointDesc = $this->getEndpointDescription($docComment);
                $endpointHttpMethod = $this->getEndpointHttpMethod($docComment);
                $endpointUri = $this->getEndpointUri($docComment);
                $endpointUriPattern = $this->getEndpointUriPattern($endpointUri);
                $endpointParams = $this->getEndpointParams($docComment);
                $endpointResponses = $this->getEndpointResponses($docComment);
                $endpointAuthProvider = $this->getEndpointAuthProvider($docComment);
                $endpointHidden = $this->getEndpointVisibility($docComment);

                // @codeCoverageIgnoreStart
                if ($endpointHttpMethod === null) {
                    $message = sprintf(
                        '[%s::%s()] @api:method not found or invalid',
                        $controllerClass,
                        $endpointMethodName
                    );
                    throw new MapBuilderException($message);
                }

                if ($endpointUri === null) {
                    $message = sprintf(
                        '[%s::%s()] @api:uri not found or invalid',
                        $controllerClass,
                        $endpointMethodName
                    );
                    throw new MapBuilderException($message);
                }

                foreach ($endpointMap as $existingEndpoint) {
                    $httpMethodMatched = $existingEndpoint['endpointHttpMethod'] === $endpointHttpMethod;
                    $uriMatched = $existingEndpoint['endpointUri'] === $endpointUri;

                    if ($httpMethodMatched && $uriMatched) {
                        $message = sprintf(
                            '[%s::%s()] method for \'%s %s\' already assigned.',
                            $controllerClass,
                            $endpointMethodName,
                            strtoupper($endpointHttpMethod),
                            $endpointUri
                        );
                        throw new MapBuilderException($message);
                    }
                }
                // @codeCoverageIgnoreEnd

                $endpointMap[$endpointMethodName] = [
                    'endpointDesc'         => $endpointDesc,
                    'endpointHttpMethod'   => $endpointHttpMethod,
                    'endpointUri'          => $endpointUri,
                    'endpointUriPattern'   => $endpointUriPattern,
                    'endpointParams'       => $endpointParams,
                    'endpointResponses'    => $endpointResponses,
                    'endpointAuthProvider' => $endpointAuthProvider,
                    'endpointHidden'       => $endpointHidden,
                ];
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            throw new ResponseException(500, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

        $endpointMapEncoded = $this->base->encode($endpointMap);
        // @codeCoverageIgnoreStart
        if ($endpointMapEncoded === false) {
            throw new ResponseException(500, 'Map encoding error');
        }
        // @codeCoverageIgnoreEnd

        $this->base->fileWrite($mapFilePath, $endpointMapEncoded);
        return $endpointMap;
    }

    /**
     * Returns endpoint description
     *
     * @param string $text
     * @return string
     */
    private function getEndpointDescription(string $text): string
    {
        preg_match('/^[^\*]+\*[^@]+@api:desc (.*?)[\r\n]?$/mi', $text, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * Returns endpoint http method
     *
     * @param string $text
     * @return null|string
     */
    private function getEndpointHttpMethod(string $text): ?string
    {
        preg_match('/^[^\*]+\*[^@]+@api:method[^\/]+(get|post|put|delete)[\r\n]?$/mi', $text, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Returns endpoint uri
     *
     * @param string $text
     * @return null|string
     */
    private function getEndpointUri(string $text): ?string
    {
        preg_match('/^[^\*]+\*[^@]+@api:uri[^\/]+(.*?)[\r\n]?$/mi', $text, $matches);
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
        $endpointUriPattern = preg_replace('/\/({[^}]+})/', '/([^/]+)', $endpointUri);
        $endpointUriPattern = preg_replace('/=({[^}]+})/', '=([^&]*)', $endpointUriPattern);
        $endpointUriPattern = str_replace('?', '\\?', $endpointUriPattern);
        return sprintf('|^%s$|i', $endpointUriPattern);
    }

    /**
     * Returns endpoint input
     *
     * @param null|string $text
     * @return array
     */
    private function getEndpointParams(?string $text): array
    {
        $endpointParams = [];

        preg_match_all('/^[^\*]+\*[^@]+@api:params:(path|query|body) (.*?)[\r\n]?$/mi', $text, $matches);
        if (count($matches) > 0) {
            foreach ($matches[1] as $idx => $value) {
                $param = explode(':', $matches[2][$idx]);
                $endpointParams[$value][] = [
                    'paramName'     => $param[0],
                    'paramType'     => $param[1],
                    'paramRequired' => isset($param[2]) && $param[2] === 'required'
                ];
            }
        }

        return $endpointParams;
    }

    /**
     * Returns endpoint output
     *
     * @param null|string $text
     * @return array
     */
    private function getEndpointResponses(?string $text): array
    {
        $endpointResponses = [];

        preg_match_all('/^[^\*]+\*[^@]+@api:response:([0-9]+)\ ?(.*?)[\r\n]?$/mi', $text, $matches);
        if (count($matches) > 0) {
            foreach ($matches[1] as $idx => $code) {
                $endpointResponses[$code] = isset($matches[2]) && isset($matches[2][$idx]) ? $matches[2][$idx] : 'void';
            }
        }

        return $endpointResponses;
    }

    /**
     * Returns endpoint auth provider
     *
     * @param null|string $text
     * @return null|string
     */
    private function getEndpointAuthProvider(?string $text): ?string
    {
        preg_match('/^[^\*]+\*[^@]+@api:auth (.*?)[\r\n]?$/mi', $text, $matches);
        return isset($matches[1]) ? $matches[1] : 'none';
    }

    /**
     * Returns endpoint auth provider
     *
     * @param null|string $text
     * @return bool
     */
    private function getEndpointVisibility(?string $text): bool
    {
        preg_match('/^[^\*]+\*[^@]+@api:hidden(.*?)[\r\n]?$/mi', $text, $matches);
        return isset($matches[1]) ? true : false;
    }
}
