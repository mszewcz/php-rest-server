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
use MS\RestServer\Server\Validators\SimpleType\ArrayValidator;

class SimpleArrayValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ArrayValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: array';
        $result = $this->validator->validate('aaa', 'array');
        $error = $result->toArray();
        $this->assertEquals($expected, $error['message']);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate([], 'array');
        $this->assertEquals($expected, $result);
    }
}
