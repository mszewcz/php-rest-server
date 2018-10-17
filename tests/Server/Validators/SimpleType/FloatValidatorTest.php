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
use MS\RestServer\Server\Validators\SimpleType\FloatValidator;

class SimpleFloatValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new FloatValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: float';
        $result = $this->validator->validate('a', 'float');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate(0.5, 'float');
        $this->assertEquals($expected, $result);
    }
}
