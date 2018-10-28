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
    protected $isAuthorized = false;
    /**
     * AbstractAuthProvider constructor.
     */
    public function __construct()
    {
    }

    /**
     * Authenticates user & returns authorization result
     *
     * @return bool
     */
    public abstract function authorize(): bool;

    /**
     * Returns authorized user
     *
     * @return AbstractUser
     */
    public abstract function getUser(): AbstractUser;
}
