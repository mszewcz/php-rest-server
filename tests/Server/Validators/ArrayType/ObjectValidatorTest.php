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
        $expected = 'Wymagany typ: object';

        $result = $this->validator->validate([0.5], 'object');
        /**
         * @var \MS\RestServer\Server\Models\ErrorModel $error
         */
        $error = $result[0];
        $error = $error->toArray();
        $this->assertEquals($expected, $error['message']);

        $result = $this->validator->validate([new \stdClass(), 'a'], 'object');
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
        $result = $this->validator->validate([new \stdClass(), new \stdClass()], 'object');
        $this->assertEquals($expected, $result);
    }
}
