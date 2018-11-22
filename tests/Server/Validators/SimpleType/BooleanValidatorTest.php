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
use MS\RestServer\Server\Validators\SimpleType\BooleanValidator;

class SimpleBooleanValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new BooleanValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: boolean';
        $result = $this->validator->validate(1, 'boolean');
        $error = $result->toArray();
        $this->assertEquals($expected, $error['message']);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate(false, 'boolean');
        $this->assertEquals($expected, $result);
    }
}
