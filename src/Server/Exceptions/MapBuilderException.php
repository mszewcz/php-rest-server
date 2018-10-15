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


class MapBuilderException extends \Exception
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * MapBuilderException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = null)
    {
        $this->message = $message;
    }
}
