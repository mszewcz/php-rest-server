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


use MS\RestServer\Server\Models\ErrorModel;

class ResponseException extends \Exception
{
    /**
     * @var int
     */
    protected $code = 0;
    /**
     * @var array
     */
    private $errors = [];

    /**
     * ResponseException constructor.
     *
     * @param int $code
     */
    public function __construct(int $code = 0)
    {
        $this->code = $code;
    }

    /**
     * @param ErrorModel $error
     */
    public function addError(ErrorModel $error): void
    {
        $this->errors[] = $error->toArray();
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
