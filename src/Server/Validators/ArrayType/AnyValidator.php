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


class AnyValidator extends AbstractArrayTypeValidator
{
    /**
     * AnyValidator constructor.
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
        return true;
    }

    /**
     * @param array $value
     * @param string $fieldName
     * @param string|null $requiredType
     * @return array
     */
    public function getErrors(array $value, string $fieldName, $requiredType = 'any'): array
    {
        return [];
    }
}
