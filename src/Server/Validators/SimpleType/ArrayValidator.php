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


class ArrayValidator extends AbstractSimpleTypeValidator
{
    /**
     * ArrayValidator constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validates value
     *
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        return is_array($value);
    }
}
