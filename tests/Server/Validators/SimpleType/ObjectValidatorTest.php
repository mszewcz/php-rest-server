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
use MS\RestServer\Server\Validators\SimpleType\ObjectValidator;

class SimpleObjectValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ObjectValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: object';
        $result = $this->validator->validate([0.5], 'object');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate(new \stdClass(), 'object');
        $this->assertEquals($expected, $result);
    }
}
