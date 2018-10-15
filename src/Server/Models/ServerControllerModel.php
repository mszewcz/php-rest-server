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
class ServerControllerModel extends AbstractModel
{
    /**
     * @api:name
     * @api:type MS\RestServer\Server\Models\ServerEndpointModel[]
     */
    public $endpoints;

    /**
     * ServerEndpointModel constructor.
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
