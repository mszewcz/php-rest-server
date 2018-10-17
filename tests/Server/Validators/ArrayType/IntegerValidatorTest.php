<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use MS\RestServer\Server\Validators\ArrayType\IntegerValidator;

class ArrayIntegerValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new IntegerValidator();
    }

    public function testValidateError()
    {
        $expected = ['Wymagany typ: integer'];
        $result = $this->validator->validate([0.5], 'integer');
        $this->assertEquals($expected, $result);

        $expected = [1 => 'Wymagany typ: integer'];
        $result = $this->validator->validate([1, 'a'], 'integer');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = [];
        $result = $this->validator->validate([2, 1], 'integer');
        $this->assertEquals($expected, $result);
    }
}
