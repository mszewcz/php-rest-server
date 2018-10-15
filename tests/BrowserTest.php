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

class BrowserTest extends TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testAddController()
    {
        $browser = new Browser();
        $browser->addController('Server', '\\MS\\RestServer\\Server\\Controllers\\Server');
        $browser->addController('Test', '\\MS\\RestServer\\Server\\Controllers\\Test');
        $result = $browser->display();
        $this->assertNotEmpty($result);
    }
}
