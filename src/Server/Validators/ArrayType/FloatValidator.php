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


class FloatValidator implements ArrayTypeValidator
{
    /**
     * Validates value
     *
     * @param array $value
     * @param string $requiredType
     * @return null|array
     */
    public function validate(array $value, string $requiredType = 'float'): ?array
    {
        $errors = [];
        foreach ($value as $key => $val) {
            if (!\is_float($val) && !\is_int($val)) {
                $errors[$key] = \sprintf('Wymagany typ: %s', $requiredType);
            }
        }
        return $errors;
    }
}
