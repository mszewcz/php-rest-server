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
use MS\RestServer\Server\Validators\SimpleType\ObjectValidator;

class SimpleObjectValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\SimpleType\AbstractSimpleTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new ObjectValidator();
    }

    public function testValidateError()
    {
        $expected = 'Wymagany typ: object';
        $result = $this->validator->validate('aaa');
        $errors = $this->validator->getErrors('field', 'object');
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
        $result = $this->validator->validate(new \stdClass());
        $this->assertEquals(true, $result);
    }
}
