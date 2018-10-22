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
class ServerEndpointModel extends AbstractModel
{
    /**
     * @api:type string
     */
    public $endpointMethodName;
    /**
     * @api:type string
     */
    public $endpointDesc;
    /**
     * @api:type string
     */
    public $endpointHttpMethod;
    /**
     * @api:type string
     */
    public $endpointUri;
    /**
     * @api:type string
     */
    public $endpointUriPattern;
    /**
     * @api:type MS\RestServer\Server\Models\ServerendpointParamsModel
     */
    public $endpointParams;
    /**
     * @api:type string
     */
    public $endpointResponses;
    /**
     * @api:type string
     */
    public $endpointAuthProvider;
    /**
     * @api:type boolean
     */
    public $endpointHidden;

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
