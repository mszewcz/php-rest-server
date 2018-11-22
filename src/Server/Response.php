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
     * @var string
     */
    private $responseEncoding = 'utf-8';

    /**
     * Response constructor.
     *
     * @param int $code
     * @param $body
     * @param string $contentType
     * @param string $encoding
     */
    public function __construct(int $code = 200, $body = '', $contentType = 'application/json', $encoding = 'utf-8')
    {
        $this->responseCode = $code;
        $this->responseBody = $body;
        $this->responseContentType = $contentType;
        $this->responseEncoding = $encoding;
    }

    /**
     * Returns response's content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->responseContentType;
    }

    /**
     * Returns response's encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->responseEncoding;
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
