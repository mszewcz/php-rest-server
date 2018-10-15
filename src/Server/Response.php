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
     * Response constructor.
     *
     * @param int $responseCode
     * @param $responseBody
     */
    public function __construct(int $responseCode = 200, $responseBody = '')
    {
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
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
