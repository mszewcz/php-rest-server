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
use MS\RestServer\Server\Validators\ArrayType\BooleanValidator;

class ArrayBooleanValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\ArrayType\AbstractArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new BooleanValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: boolean';
        $result = $this->validator->validate([1]);
        $errors = $this->validator->getErrors([1],'field', 'boolean');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field.0', ($error->toArray())['field']);

        $result = $this->validator->validate([true, 1]);
        $errors = $this->validator->getErrors([true, 1],'field', 'boolean');
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
        $result = $this->validator->validate([true, false]);
        $this->assertEquals(true, $result);
    }
}
