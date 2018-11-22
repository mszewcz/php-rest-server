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


use MS\RestServer\Server\Models\ErrorModel;

class AuthorizationResult
{
    /**
     * @var bool
     */
    private $result = false;
    /**
     * @var ErrorModel
     */
    private $error;
    /**
     * @var AbstractUser
     */
    private $user;

    /**
     * AuthorizationResult constructor.
     *
     * @param bool $result
     * @param ErrorModel|null $error
     * @param AbstractUser|null $user
     */
    public function __construct(bool $result, ?ErrorModel $error, ?AbstractUser $user)
    {
        $this->result = $result;
        $this->error = $error;
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
     * @return ErrorModel|null
     */
    public function getError(): ?ErrorModel
    {
        return $this->error;
    }

    /**
     * @return AbstractUser|null
     */
    public function getUser(): ?AbstractUser
    {
        return $this->user;
    }
}
