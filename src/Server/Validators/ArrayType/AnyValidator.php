<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\ArrayType;

use MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator;


class AnyValidator implements ArrayTypeValidator
{
    /**
     * Validates value
     *
     * @param array $value
     * @param string $requiredType
     * @param string $fieldName
     * @return array|null
     */
    public function validate(array $value, $requiredType = 'any', $fieldName = null): ?array
    {
        return [];
    }
}
