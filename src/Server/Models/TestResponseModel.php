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
class TestResponseModel
{
    /**
     * @api:type MS\RestServer\Server\Models\TestRequestParamModel[]
     */
    public $urlParams = [];
    /**
     * @api:type MS\RestServer\Server\Models\TestRequestBodyModel
     */
    public $requestBody;

    /**
     * TestResponseModel constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        if (\is_array($data)) {
            $this->urlParams = isset($data['urlParams']) ? $data['urlParams'] : [];
            $this->requestBody = isset($data['requestBody']) ? $data['requestBody'] : new \stdClass();
        }
    }
}
