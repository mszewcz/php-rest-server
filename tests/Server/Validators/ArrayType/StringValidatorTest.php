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
use MS\RestServer\Server\Validators\ArrayType\StringValidator;

class ArrayStringValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new StringValidator();
    }

    public function testValidateError()
    {
        $expected = ['Wymagany typ: string'];
        $result = $this->validator->validate([true], 'string');
        $this->assertEquals($expected, $result);

        $expected = [1 => 'Wymagany typ: string'];
        $result = $this->validator->validate(['a', 1], 'string');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = [];
        $result = $this->validator->validate(['a', 'a'], 'string');
        $this->assertEquals($expected, $result);
    }
}
