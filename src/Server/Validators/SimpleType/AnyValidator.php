<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\SimpleType;

use MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator;


class AnyValidator implements SimpleTypeValidator
{
    /**
     * Validates value
     *
     * @param $value
     * @param string $requiredType
     * @return null|string
     */
    public function validate($value, string $requiredType = 'any'): ?string
    {
        if (is_null($value)) {
            return 'To pole jest wymagane';
        }
        return null;
    }
}
