<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Helpers;


class DataTypeHelper
{
    /**
     * @var array
     */
    private $simpleTypes = [
        'any', 'array', 'bool', 'boolean', 'double', 'float', 'int', 'integer', 'string'
    ];
    /**
     * @var array
     */
    private $simpleArrayTypes = [
        'any[]', 'array[]', 'bool[]', 'boolean[]', 'double[]', 'float[]', 'int[]', 'integer[]', 'string[]'
    ];

    /**
     * DataTypeHelper constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $type
     * @return string
     */
    public function getDataType(string $type): string
    {
        if (in_array($type, $this->simpleTypes)) {
            return $type;
        }
        if (in_array($type, $this->simpleArrayTypes)) {
            return sprintf('Array&lt;%s&gt;', str_replace('[]', '', $type));
        }

        $type = $this->getModelName($type);

        if ($this->isArrayType($type)) {
            return sprintf('Array&lt;%s&gt;', str_replace('[]', '', $type));
        }
        return $type;
    }

    /**
     * Returns simple model name
     *
     * @param string $type
     * @return string
     */
    public function getSimpleModelName(string $type): string
    {
        $modelName = $this->getModelName($type);
        return str_replace('[]', '', $modelName);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isModelType(string $type): bool {
        if (in_array($type, $this->simpleTypes)) {
            return false;
        }
        if (in_array($type, $this->simpleArrayTypes)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getModelName(string $type): string
    {
        $typeExploded = explode('\\', $type);
        return array_pop($typeExploded);
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isArrayType(string $type): bool {
        return stripos($type, '[]') !== false;
    }
}
