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
use MS\RestServer\Server\Validators\ArrayType\AnyValidator;

class ArrayAnyValidatorTest extends TestCase
{
    /**
     * @var \MS\RestServer\Server\Validators\ArrayType\AbstractArrayTypeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new AnyValidator();
    }

    public function testValidateError()
    {
        $result = $this->validator->validate([null]);
        $this->assertEquals(true, $result);
    }

    public function testValidateOK()
    {
        $result = $this->validator->validate([]);
        $this->assertEquals(true, $result);
    }
}
