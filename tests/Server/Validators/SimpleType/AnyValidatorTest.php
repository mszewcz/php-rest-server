<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace SimpleTypeTests;

use PHPUnit\Framework\TestCase;
use MS\RestServer\Server\Validators\SimpleType\AnyValidator;

class SimpleAnyValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new AnyValidator();
    }

    public function testValidateError()
    {
        $expected = null;
        $result = $this->validator->validate(null, 'any');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate('aaa', 'any');
        $this->assertEquals($expected, $result);
    }
}
