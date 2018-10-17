<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Models;


/**
 * @codeCoverageIgnore
 */
class TestRequestBodyModel extends AbstractModel
{
    /**
     * @api:type string
     */
    public $testVar1;
    /**
     * @api:type string
     */
    public $testVar2;
    /**
     * @api:type string
     */
    public $testVar3;

    /**
     * TestRequestBodyModel constructor.
     * @param null|array $data
     */
    public function __construct($data)
    {
        if (\is_array($data)) {
            $this->testVar1 = isset($data['testVar1']) ? $data['testVar1'] : null;
            $this->testVar2 = isset($data['testVar2']) ? $data['testVar2'] : null;
            $this->testVar3 = isset($data['testVar3']) ? $data['testVar3'] : null;
        }
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        if ($this->testVar1 === null) {
            $errors['testVar1'] = 'To pole jest wymagane';
        } elseif (!is_int($this->testVar1)) {
            $errors['testVar1'] = 'Wymagana liczba';
        }
        if ($this->testVar2 === null) {
            $errors['testVar2'] = 'To pole jest wymagane';
        }
        if ($this->testVar3 === null) {
            $errors['testVar3'] = 'To pole jest wymagane';
        }
        return $errors;
    }
}
