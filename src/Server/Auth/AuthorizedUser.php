<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Auth;


class AuthorizedUser
{
    /**
     * @var array
     */
    private $userData = [];
    /**
     * @var string
     */
    private $idKey = 'id';

    /**
     * AuthorizedUser constructor.
     *
     * @param array $userData
     * @param string $idKey
     */
    public function __construct(array $userData, string $idKey)
    {
        $this->userData = $userData;
        $this->idKey = $idKey;
    }

    /**
     * @return mixed|null
     */
    public function id()
    {
        return isset($this->userData[$this->idKey]) ? $this->userData[$this->idKey] : null;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function data(string $name)
    {
        return isset($this->userData[$name]) ? $this->userData[$name] : null;
    }
}
