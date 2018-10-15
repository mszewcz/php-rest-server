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

use MS\RestServer\Server\Models\TestRequestBodyModel;
use MS\RestServer\Server\Models\TestRequestParamModel;
use MS\RestServer\Server\Models\TestResponseModel;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Response;

/**
 * @codeCoverageIgnore
 */
class Test extends AbstractController
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
     * @api:desc Returns sum of a & b
     * @api:method get
     * @api:uri /test/sum-of/{a}/and/{b}
     * @api:input:path a:integer:required
     * @api:input:path b:integer:required
     * @api:output integer
     *
     * @return Response
     */
    protected function sumOf(): Response
    {
        $urlParams = $this->request->getRequestPathParams();
        $sum = (int)$urlParams['a'] + (int)$urlParams['b'];

        return new Response(200, $sum);
    }

    /**
     * @api:desc Server ping test - returns response containing received url params & request body
     * @api:method post
     * @api:uri /test/ping/{intParam1}/{intParam2}
     * @api:input:path intParam1:integer:required
     * @api:input:path intParam2:integer:required
     * @api:input:body testBody:MS\RestServer\Server\Models\TestRequestBodyModel:required
     * @api:output MS\RestServer\Server\Models\TestResponseModel
     *
     * @return Response
     */
    protected function ping(): Response
    {
        $tmpParams = $this->request->getRequestPathParams();
        $urlParams = [];
        foreach ($tmpParams as $paramName => $paramValue) {
            $urlParams[] = new TestRequestParamModel([$paramName => $paramValue]);
        }
        $requestBody = new TestRequestBodyModel($this->request->getRequestBody());

        $response = [
            'urlParams'   => $urlParams,
            'requestBody' => $requestBody
        ];

        return new Response(200, new TestResponseModel($response));
    }
}
