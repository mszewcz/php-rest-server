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


abstract class AbstractAuthProvider
{
    /**
     * AbstractAuthProvider constructor.
     */
    public function __construct()
    {
    }

    /**
     * Returns user's authorization status
     *
     * @return bool
     */
    public abstract function isAuthorized(): bool;
}
