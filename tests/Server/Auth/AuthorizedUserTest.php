<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Auth\AbstractUser;
use MS\RestServer\Server\Auth\AuthorizedUser;
use PHPUnit\Framework\TestCase;

class AuthorizedUserTest extends TestCase
{
    /**
     * @var AbstractUser
     */
    private $user;

    public function setUp()
    {
        $this->user = new AuthorizedUser();
    }

    public function testId()
    {
        $this->assertEquals(3, $this->user->id());
    }
}
