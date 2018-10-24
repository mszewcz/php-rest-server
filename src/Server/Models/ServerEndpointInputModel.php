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
class ServerEndpointParamsModel extends AbstractModel
{
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointParamsParamModel[]
     * @api:optional
     */
    public $path;
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointParamsParamModel[]
     * @api:optional
     */
    public $query;
    /**
     * @api:type MS\RestServer\Server\Models\ServerEndpointParamsParamModel[]
     * @api:optional
     */
    public $body;

    /**
     * ServerEndpointParamsModel constructor.
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
