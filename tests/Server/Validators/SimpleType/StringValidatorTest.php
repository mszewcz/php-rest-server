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
use MS\RestServer\Server\Validators\SimpleType\StringValidator;

class SimpleStringValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\SimpleType\AbstractSimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new StringValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: string';
        $result = $this->validator->validate(3);
        $errors = $this->validator->getErrors('field', 'string');
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
        $result = $this->validator->validate('a');
        $this->assertEquals(true, $result);
    }
}
