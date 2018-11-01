<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\MapBuilder;
use PHPUnit\Framework\TestCase;

$_ENV['CONFIG_FILE_SERVER'] = realpath(dirname(__FILE__) . '/../') . '/src/_config_server.json';

class MapBuilderTest extends TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * @throws \MS\RestServer\Server\Exceptions\ResponseException
     */
    public function testBuild()
    {
        $mapBuilder = new MapBuilder();
        $result = $mapBuilder->build();
        $expected = [
            [
                'rebuildEndpointsMap' => [
                    'endpointDesc'         => 'Re-builds server endpoints\' map',
                    'endpointHttpMethod'   => 'get',
                    'endpointUri'          => '/api/rebuildEndpointsMap',
                    'endpointUriPattern'   => '|^/api/rebuildEndpointsMap$|i',
                    'endpointParams'       => [],
                    'endpointResponses'    => [200 => 'MS\\RestServer\\Server\\Models\\ServerControllerModel[]'],
                    'endpointAuthProvider' => 'none',
                    'endpointHidden'       => false
                ]
            ],
            [
                'sumOf' => [
                    'endpointDesc'         => 'Returns sum of a & b',
                    'endpointHttpMethod'   => 'get',
                    'endpointUri'          => '/test/sum-of/{a}/and/{b}',
                    'endpointUriPattern'   => '|^/test/sum-of/([^/]+)/and/([^/]+)$|i',
                    'endpointParams'       => [
                        'path' => [
                            [
                                'paramName'     => 'a',
                                'paramType'     => 'integer',
                                'paramRequired' => true
                            ],
                            [
                                'paramName'     => 'b',
                                'paramType'     => 'integer',
                                'paramRequired' => true
                            ]
                        ]
                    ],
                    'endpointResponses'    => [200 => 'integer'],
                    'endpointAuthProvider' => 'none',
                    'endpointHidden'       => false
                ],
                'ping'  => [
                    'endpointDesc'         =>
                        'Server ping test - returns response containing received url params & request body',
                    'endpointHttpMethod'   => 'post',
                    'endpointUri'          => '/test/ping/{intParam1}/{intParam2}',
                    'endpointUriPattern'   => '|^/test/ping/([^/]+)/([^/]+)$|i',
                    'endpointParams'       => [
                        'path' => [
                            [
                                'paramName'     => 'intParam1',
                                'paramType'     => 'integer',
                                'paramRequired' => true
                            ],
                            [
                                'paramName'     => 'intParam2',
                                'paramType'     => 'integer',
                                'paramRequired' => true
                            ]
                        ],
                        'body' => [
                            [
                                'paramName'     => 'testBody',
                                'paramType'     => 'MS\\RestServer\\Server\\Models\\TestRequestBodyModel',
                                'paramRequired' => true
                            ]
                        ]
                    ],
                    'endpointResponses'    => [200 => 'MS\\RestServer\\Server\\Models\\TestResponseModel'],
                    'endpointAuthProvider' => 'none',
                    'endpointHidden'       => false
                ]
            ]
        ];
        $this->assertEquals($expected, $result);
    }
}
