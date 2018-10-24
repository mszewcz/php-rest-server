<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Auth\AuthorizedUser;
use PHPUnit\Framework\TestCase;

class AuthorizedUserTest extends TestCase
{
    /**
     * @var AuthorizedUser
     */
    private $user;

    public function setUp()
    {
        $userData = [
            'id'   => 3,
            'name' => 'test'
        ];
        $this->user = new AuthorizedUser($userData, 'id');
    }

    public function testId()
    {
        $this->assertEquals(3, $this->user->id());
    }

    public function testData()
    {
        $this->assertEquals('test', $this->user->data('name'));
    }
}
