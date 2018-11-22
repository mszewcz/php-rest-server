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
use MS\RestServer\Server\Validators\SimpleType\IntegerValidator;

class SimpleIntegerValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new IntegerValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: integer';
        $result = $this->validator->validate([0.5], 'integer');
        $error = $result->toArray();
        $this->assertEquals($expected, $error['message']);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate(2, 'integer');
        $this->assertEquals($expected, $result);
    }
}
