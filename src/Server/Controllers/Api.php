<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Controllers;

use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Server\MapBuilder;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Response;

/**
 * @codeCoverageIgnore
 */
class Api extends AbstractController
{
    /**
     * Server constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * @api:desc Re-builds server endpoints' map
     * @api:method get
     * @api:uri /api/rebuildEndpointsMap
     * @api:response:200 MS\RestServer\Server\Models\ServerControllerModel[]
     *
     * @return Response
     * @throws ResponseException
     */
    protected function rebuildEndpointsMap(): Response
    {
        $mapBuilder = new MapBuilder();
        $result = $mapBuilder->build();

        return new Response(200, $result);
    }
}
