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
use MS\RestServer\Server\Validators\RequiredType\RequiredValidator;

class RequiredValidatorTest extends TestCase
{
    /**
     * @var RequiredValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new RequiredValidator();
    }

    public function testValidateError()
    {
        $expected = 'To pole jest wymagane';
        $result = $this->validator->validate(null);
        $errors = $this->validator->getErrors('field');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $errors[0];
        $this->assertEquals(false, $result);
        $this->assertEquals($expected, ($error->toArray())['message']);
        $this->assertEquals('field', ($error->toArray())['field']);

        $result = $this->validator->validate('');
        $errors = $this->validator->getErrors('field');
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

        $result = $this->validator->validate([]);
        $this->assertEquals(true, $result);

        $result = $this->validator->validate(4);
        $this->assertEquals(true, $result);

        $result = $this->validator->validate(1.2);
        $this->assertEquals(true, $result);

        $result = $this->validator->validate(true);
        $this->assertEquals(true, $result);

        $result = $this->validator->validate('a');
        $this->assertEquals(true, $result);
    }
}
