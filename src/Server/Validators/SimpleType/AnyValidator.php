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

use MS\RestServer\Server\Models\ErrorModel;
use MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator;


class AnyValidator implements SimpleTypeValidator
{
    /**
     * Validates value

     * @param $value
     * @param string $requiredType
     * @param string $fieldName
     * @return ErrorModel|null
     */
    public function validate($value, $requiredType = 'any', $fieldName = null): ?ErrorModel
    {
        return null;
    }
}
