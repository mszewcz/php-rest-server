<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    private $response;

    public function setUp()
    {
        $this->response = new Response(200, 'OK');
    }

    public function tearDown()
    {
    }

    public function testGetCode()
    {
        $this->assertEquals($this->response->getCode(), 200);
    }

    public function testGetBody()
    {
        $this->assertEquals($this->response->getBody(), 'OK');
    }
}
