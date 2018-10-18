<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

require_once realpath(dirname(__FILE__) . '/../../') . '/vendor/autoload.php';

$_ENV['CONFIG_FILE_FRAMEWORK'] = realpath(dirname(__FILE__) . '/../../') . '/src/_config_framework.json';
$_ENV['CONFIG_FILE_SERVER'] = realpath(dirname(__FILE__) . '/../../') . '/src/_config_server.json';

$server = new \MS\RestServer\Server();
echo filter_var($server->getResponse(), FILTER_DEFAULT);
