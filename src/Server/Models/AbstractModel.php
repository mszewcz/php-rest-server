<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Models;


abstract class AbstractModel
{
    /**
     * Abstract validation method
     *
     * @return array
     */
    public abstract function validate(): array;
}
