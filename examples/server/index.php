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

$server = new \MS\RestServer\Server();
$server->addController('server', '\\MS\\RestServer\\Server\\Controllers\\Server');
$server->addController('test', '\\MS\\RestServer\\Server\\Controllers\\Test');
$server->handleRequest();

