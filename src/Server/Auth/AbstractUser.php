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


abstract class AbstractUser
{
    /**
     * @var int
     */
    protected $userId = 0;

    /**
     * @return int
     */
    public function id()
    {
        return $this->userId;
    }
}
