<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Models;

use MS\RestServer\Server\Exceptions\ResponseException;


abstract class AbstractModel
{
    /**
     * Abstract validation method
     *
     * @return array
     */
    public abstract function validate(): array;

    /**
     * Abstract function called before $this->asArray()
     *
     * @return void
     */
    public abstract function beforeAsArray(): void;

    /**
     * @return array
     * @throws ResponseException
     */
    public function asArray(): array
    {
        try {
            $this->beforeAsArray();

            $reflectionClass = new \ReflectionClass($this);
            $classProperties = $reflectionClass->getProperties();
            $data = [];

            foreach ($classProperties as $classProperty) {
                $propertyName = (string) $classProperty->getName();
                $data[$propertyName] = $this->getPropertyValue($propertyName);
            }

            return $data;
        } catch (\ReflectionException $exception) {
            throw new ResponseException(500, $exception->getMessage());
        }
    }

    /**
     * @param $propertyName
     * @return null
     */
    private function getPropertyValue($propertyName)
    {
        return property_exists($this, $propertyName) ? $this->$propertyName : null;
    }
}
