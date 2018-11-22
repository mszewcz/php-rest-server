<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\Interfaces;

use MS\RestServer\Server\Models\ErrorModel;


interface SimpleTypeValidator
{
    public function validate($value, $requiredType = 'any'): ?ErrorModel;
}
