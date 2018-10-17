<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace ArrayTypeTests;

use PHPUnit\Framework\TestCase;
use MS\RestServer\Server\Validators\SimpleType\StringValidator;

class SimpleStringValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new StringValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: string';
        $result = $this->validator->validate(true, 'string');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = null;
        $result = $this->validator->validate('a', 'string');
        $this->assertEquals($expected, $result);
    }
}
