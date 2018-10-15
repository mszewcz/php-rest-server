<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Exceptions;


class ResponseException extends \Exception
{
    /**
     * @var int
     */
    protected $code = 0;
    /**
     * @var string
     */
    protected $message = '';
    /**
     * @var array
     */
    private $errors = [];

    /**
     * ResponseException constructor.
     *
     * @param int $code
     * @param string $message
     * @param array $errors
     */
    public function __construct(int $code = 0, string $message = null, array $errors = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->errors = $errors;
    }

    /**
     * Returns response errors
     *
     * @return array
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
