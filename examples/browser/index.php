<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

require_once \realpath(\dirname(__FILE__) . '/../../') . '/vendor/autoload.php';

$_ENV['CONFIG_FILE_FRAMEWORK'] = \realpath(\dirname(__FILE__) . '/../../') . '/src/_config_framework.json';

$browser = new \MS\RestServer\Browser();
$browser->addController('Server', '\\MS\\RestServer\\Server\\Controllers\\Server');
$browser->addController('Test', '\\MS\\RestServer\\Server\\Controllers\\Test');
echo $browser->display();
