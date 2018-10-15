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

use MS\RestServer\Server\Exceptions\MapBuilderException;
use PHPUnit\Framework\TestCase;

class MapBuilderExceptionTest extends TestCase
{
    /**
     * @throws MapBuilderException
     */
    public function testExceptionMessage()
    {
        $this->expectExceptionMessage('Exception text');
        throw new MapBuilderException('Exception text');
    }
}
