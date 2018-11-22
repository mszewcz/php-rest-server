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
use MS\RestServer\Server\Validators\SimpleType\FloatValidator;

class SimpleFloatValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\SimpleType\AbstractSimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new FloatValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: float';
        $result = $this->validator->validate('aaa');
        $errors = $this->validator->getErrors('field', 'float');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field', ($error->toArray())['field']);
    }

    public function testValidateOK()
    {
        $result = $this->validator->validate(0.5);
        $this->assertEquals(true, $result);

        $result = $this->validator->validate(1);
        $this->assertEquals(true, $result);
    }
}
