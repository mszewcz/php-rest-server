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
class ServerEndpointInputParamModel extends AbstractModel
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
     * ServerEndpointInputParamModel constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        return [];
    }
}
