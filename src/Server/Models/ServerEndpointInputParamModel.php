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
class ServerendpointParamsParamModel extends AbstractModel
{
    /**
     * @api:type string
     */
    public $paramName;
    /**
     * @api:type string
     */
    public $paramType;
    /**
     * @api:type boolean
     */
    public $paramRequired;

    /**
     * ServerendpointParamsParamModel constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $data = null;
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        return [];
    }
}
