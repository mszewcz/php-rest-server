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
class Server extends AbstractController
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
     * @api:uri /server/rebuildEndpointsMap
     * @api:output MS\RestServer\Server\Models\ServerControllerModel[]
     *
     * @return Response
     * @throws ResponseException
     */
    protected function rebuildEndpointsMap(): Response
    {
        $mapBuilder = new MapBuilder();
        $controllersMap = $this->request->getControllersMap();

        foreach ($controllersMap as $controllerName => $controllerClass) {
            $mapBuilder->addController($controllerName, $controllerClass);
        }

        try {
            $result = $mapBuilder->buildMaps();
        } catch (ResponseException $e) {
            throw $e;
        }

        return new Response(200, $result);
    }
}
