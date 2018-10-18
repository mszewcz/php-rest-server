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
class TestRequestParamModel extends AbstractModel
{
    /**
     * @api:type string
     */
    public $name;
    /**
     * @api:type any
     */
    public $value;

    /**
     * TestRequestParamModel constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        if (is_array($data)) {
            foreach ($data as $paramName => $paramValue) {
                $this->name = $paramName;
                $this->value = (int)$paramValue;
            }
        }
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        return [];
    }
}
