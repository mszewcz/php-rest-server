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
class ServerEndpointInputModel extends AbstractModel
{
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointInputParamModel[]
     * @api:optional
     */
    public $path;
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointInputParamModel[]
     * @api:optional
     */
    public $query;
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointInputParamModel[]
     * @api:optional
     */
    public $body;

    /**
     * ServerEndpointInputModel constructor.
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
