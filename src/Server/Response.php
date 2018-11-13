<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server;


class Response
{
    /**
     * @var int
     */
    private $responseCode = 0;
    /**
     * @var
     */
    private $responseBody;
    /**
     * @var string
     */
    private $responseContentType = 'application/json';

    /**
     * Response constructor.
     *
     * @param int $responseCode
     * @param $responseBody
     * @param string $contentType
     */
    public function __construct(int $responseCode = 200, $responseBody = '', $contentType = 'application/json')
    {
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
        $this->responseContentType = sprintf('%s; charset=utf-8', $contentType);
    }

    /**
     * Returns response's body
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->responseContentType;
    }

    /**
     * Returns response's code
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->responseCode;
    }

    /**
     * Returns response's body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->responseBody;
    }
}
