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
use MS\RestServer\Server\Models\ErrorModel;
use PHPUnit\Framework\TestCase;

class ResponseExceptionTest extends TestCase
{
    /**
     * @throws ResponseException
     */
    public function testExceptionCode()
    {
        $this->expectExceptionCode(500);
        throw new ResponseException(500);
    }

    public function testGetErrors()
    {
        $exception = new ResponseException(500);
        $exception->addError(new ErrorModel(
            100,
            'Error Message',
            'Field name'
        ));

        $this->assertEquals(
            [['code' => 100, 'message' => 'Error Message', 'field' => 'Field name']],
            $exception->getErrors()
        );
    }
}
