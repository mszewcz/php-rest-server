<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Browser;
use PHPUnit\Framework\TestCase;

$_ENV['CONFIG_FILE_SERVER'] = realpath(dirname(__FILE__) . '/../') . '/src/_config_server.json';


class BrowserTest extends TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testDisplay()
    {
        $browser = new Browser();
        $result = $browser->display();
        $this->assertNotEmpty($result);
    }
}
