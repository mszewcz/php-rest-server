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
     * @var \MS\RestServer\Server\Validators\ArrayType\AbstractArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new IntegerValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: integer';
        $result = $this->validator->validate(['a']);
        $errors = $this->validator->getErrors(['a'],'field', 'integer');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field.0', ($error->toArray())['field']);

        $result = $this->validator->validate([1, 'a']);
        $errors = $this->validator->getErrors([1, 'a'],'field', 'integer');
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
        $result = $this->validator->validate([2, 1]);
        $this->assertEquals(true, $result);
    }
}
