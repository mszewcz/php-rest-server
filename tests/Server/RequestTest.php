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
    public function authorize(): bool
    {
        return true;
    }

    public function getUser(): \MS\RestServer\Server\Auth\AuthorizedUser
    {
        return new \MS\RestServer\Server\Auth\AuthorizedUser([], 'id');
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

    public function testSetGetRequestAuthProvider()
    {
        $authProvider = new TestAuthProvider();
        $this->request->setDefaultAuthProvider($authProvider);
        $this->assertEquals($this->request->getDefaultAuthProvider(), $authProvider);
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

    public function testGetRequestPathParams()
    {
        $this->assertEquals($this->request->getRequestPathParams(), []);
    }

    public function testGetRequestQueryParams()
    {
        $this->assertEquals($this->request->getRequestQueryParams(), []);
    }

    public function testGetRequestBody()
    {
        $this->assertEquals($this->request->getRequestBody(), null);
    }
}
