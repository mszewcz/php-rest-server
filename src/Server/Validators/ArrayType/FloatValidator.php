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


class FloatValidator extends AbstractArrayTypeValidator
{
    /**
     * FloatValidator constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validates value
     *
     * @param array $value
     * @return bool
     */
    public function validate(array $value): bool
    {
        foreach ($value as $val) {
            if (!is_float($val) && !is_int($val)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $value
     * @param string $fieldName
     * @param string $requiredType
     * @return array
     */
    public function getErrors(array $value, string $fieldName, $requiredType = 'float'): array
    {
        $errors = [];
        foreach ($value as $index => $val) {
            if (!is_float($val) && !is_int($val)) {
                $errors[] = $this->getErrorModel($fieldName, $index, $requiredType);
            }
        }
        return $errors;
    }
}
