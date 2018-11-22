<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Auth\AuthorizationResult;
use MS\RestServer\Server\Auth\AuthorizedUser;
use MS\RestServer\Server\Models\ErrorModel;
use PHPUnit\Framework\TestCase;

class AuthorizationResultTest extends TestCase
{
    public function setUp()
    {
    }

    public function testGetResult()
    {
        $result = new AuthorizationResult(true, null, new AuthorizedUser());

        $this->assertEquals(true, $result->getResult());
        $this->assertEquals(null, $result->getError());
        $this->assertEquals(0, $result->getUser()->id());
    }

    public function testGetUser()
    {
        $result = new AuthorizationResult(true, null, new AuthorizedUser());

        $this->assertEquals(true, $result->getResult());
        $this->assertEquals(null, $result->getError());
        $this->assertEquals(0, $result->getUser()->id());
    }

    public function testGetError()
    {
        $result = new AuthorizationResult(true, new ErrorModel(300), null);

        $this->assertEquals(true, $result->getResult());
        $this->assertEquals(300, $result->getError()->toArray()['code']);
        $this->assertEquals(null, $result->getUser());
    }
}
