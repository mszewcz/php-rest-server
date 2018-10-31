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


class AuthorizationResult
{
    /**
     * @var bool
     */
    private $result = false;
    /**
     * @var string
     */
    private $errorMessage;
    /**
     * @var AbstractUser
     */
    private $user;

    /**
     * AuthorizationResult constructor.
     *
     * @param bool $result
     * @param string|null $errorMessage
     * @param AbstractUser $user
     */
    public function __construct(bool $result, ?string $errorMessage, AbstractUser $user)
    {
        $this->result = $result;
        $this->errorMessage = $errorMessage;
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return AbstractUser
     */
    public function getUser(): AbstractUser
    {
        return $this->user;
    }
}
