<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Exceptions;

use MS\RestServer\Server\Exceptions\ResponseException;
use PHPUnit\Framework\TestCase;

class ResponseExceptionTest extends TestCase
{
    /**
     * @throws ResponseException
     */
    public function testExceptionMessage()
    {
        $this->expectExceptionMessage('Internal Server Error');
        throw new ResponseException(500, 'Internal Server Error', []);
    }

    public function testGetErrors()
    {
        $exception = new ResponseException(500, 'Internal Server Error', ['message'=>'text']);
        $this->assertEquals(['message'=>'text'], $exception->getErrors());
    }
}
