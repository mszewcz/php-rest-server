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
use MS\RestServer\Server\Validators\ArrayType\ObjectValidator;

class ArrayObjectValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ObjectValidator();
    }

    public function testValidateError()
    {
        $expected = ['Wymagany typ: object'];
        $result = $this->validator->validate([0.5], 'object');
        $this->assertEquals($expected, $result);

        $expected = [1 => 'Wymagany typ: object'];
        $result = $this->validator->validate([new \stdClass(), 'a'], 'object');
        $this->assertEquals($expected, $result);
    }

    public function testValidateOK()
    {
        $expected = [];
        $result = $this->validator->validate([new \stdClass(), new \stdClass()], 'object');
        $this->assertEquals($expected, $result);
    }
}
