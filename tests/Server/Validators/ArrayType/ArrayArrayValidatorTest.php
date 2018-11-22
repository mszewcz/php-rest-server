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
     * @var \MS\RestServer\Server\Validators\ArrayType\AbstractArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ArrayValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: array';
        $result = $this->validator->validate([1]);
        $errors = $this->validator->getErrors([1],'field', 'array');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field.0', ($error->toArray())['field']);

        $result = $this->validator->validate([[], 1]);
        $errors = $this->validator->getErrors([[], 1],'field', 'array');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field.1', ($error->toArray())['field']);
    }

    public function testValidateOK()
    {
        $result = $this->validator->validate([[], []]);
        $this->assertEquals(true, $result);
    }
}
