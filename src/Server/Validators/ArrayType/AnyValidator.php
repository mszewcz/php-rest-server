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
     * @return null|array
     */
    public function validate(array $value, string $requiredType = 'any'): ?array
    {
        $errors = [];
        foreach ($value as $key => $val) {
            if (\is_null($val)) {
                $errors[$key] = 'To pole jest wymagane';
            }
        }
        return $errors;
    }
}
