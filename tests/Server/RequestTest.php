<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Request;
use PHPUnit\Framework\TestCase;

class TestAuthProvider extends \MS\RestServer\Server\Auth\AbstractAuthProvider
{
    public function isAuthorized(): bool
    {
        return true;
    }
}

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        try {
            $this->request = new Request();
        } catch (\Exception $e) {
        }
    }

    public function tearDown()
    {
    }

    public function testSetGetControllersMap()
    {
        $map = [
            'system' => '\\MS\\RestServer\\Server\\Controllers\\Test'
        ];
        $this->request->setControllersMap($map);
        $this->assertEquals($this->request->getControllersMap(), $map);
    }

    public function testSetGetRequestAuthProvider()
    {
        $authProvider = new TestAuthProvider();
        $this->request->setDefaultAuthProvider($authProvider);
        $this->assertEquals($this->request->getDefaultAuthProvider(), $authProvider);
    }

    public function testGetPathArray()
    {
        $this->assertEquals($this->request->getPathArray(), []);
    }

    public function testGetRequestMethod()
    {
        $this->assertEquals($this->request->getRequestHttpMethod(), 'get');
    }

    public function testSetGetRequestUri()
    {
        $this->assertEquals($this->request->getRequestUri(), '');
    }

    public function testGetRequestController()
    {
        $this->assertEquals($this->request->getRequestControllerName(), '');
    }

    public function testSetGetRequestUrlParams()
    {
        $this->request->setPathParam('a', 1);
        $this->assertEquals($this->request->getRequestPathParams(), ['a' => 1]);
    }

    public function testGetRequestGetParams()
    {
        $this->assertEquals($this->request->getRequestQueryParams(), []);
    }

    public function testGetRequestBody()
    {
        $this->assertEquals($this->request->getRequestBody(), null);
    }
}
