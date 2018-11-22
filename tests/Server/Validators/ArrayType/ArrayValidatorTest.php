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
use MS\RestServer\Server\Validators\ArrayType\ArrayValidator;

class ArrayArrayValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ArrayValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: array';
        $result = $this->validator->validate([1], 'array');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $result[0];
        $error = $error->toArray();
        $this->assertEquals($expected, $error['message']);

        $result = $this->validator->validate([[], 1], 'array');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $result[1];
        $error = $error->toArray();
        $this->assertEquals($expected, $error['message']);
    }

    public function testValidateOK()
    {
        $expected = [];
        $result = $this->validator->validate([[], []], 'array');
        $this->assertEquals($expected, $result);
    }
}
