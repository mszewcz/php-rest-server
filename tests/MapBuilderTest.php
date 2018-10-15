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
    public function testAddController()
    {
        $mapBuilder = new MapBuilder();
        $mapBuilder->addController('Server', '\\MS\\RestServer\\Server\\Controllers\\Server');
        $mapBuilder->addController('Test', '\\MS\\RestServer\\Server\\Controllers\\Test');
        $result = $mapBuilder->buildMaps();
        $expected = [[['endpointMethodName'=>'rebuildEndpointsMap','endpointDesc'=>'Re-builds server endpoints\' map','endpointHttpMethod'=>'get','endpointUri'=>'/server/rebuildEndpointsMap','endpointUriPattern'=>'|^/server/rebuildEndpointsMap$|i','endpointInput'=>[],'endpointOutput'=>'MS\\RestServer\\Server\\Models\\ServerControllerModel[]','endpointAuthProvider'=>'none']],[['endpointMethodName'=>'sumOf','endpointDesc'=>'Returns sum of a & b','endpointHttpMethod'=>'get','endpointUri'=>'/test/sum-of/{a}/and/{b}','endpointUriPattern'=>'|^/test/sum-of/([^/]+)/and/([^/]+)$|i','endpointInput'=>['path'=>[['paramName'=>'a','paramType'=>'integer','paramRequired'=>true],['paramName'=>'b','paramType'=>'integer','paramRequired'=>true]]],'endpointOutput'=>'integer','endpointAuthProvider'=>'none'],['endpointMethodName'=>'ping','endpointDesc'=>'Server ping test - returns response containing received url params & request body','endpointHttpMethod'=>'post','endpointUri'=>'/test/ping/{intParam1}/{intParam2}','endpointUriPattern'=>'|^/test/ping/([^/]+)/([^/]+)$|i','endpointInput'=>['path'=>[['paramName'=>'intParam1','paramType'=>'integer','paramRequired'=>true],['paramName'=>'intParam2','paramType'=>'integer','paramRequired'=>true]],'body'=>[['paramName'=>'testBody','paramType'=>'MS\\RestServer\\Server\\Models\\TestRequestBodyModel','paramRequired'=>true]]],'endpointOutput'=>'MS\\RestServer\\Server\\Models\\TestResponseModel','endpointAuthProvider'=>'none']]];
        $this->assertEquals($expected, $result);
    }
}
