<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Browser;

use MS\RestServer\Server\Helpers\DataTypeHelper;


class ModelDescriber
{
    /**
     * @var DataTypeHelper
     */
    private $dataTypeHelper;

    /**
     * ModelDescriber constructor.
     */
    public function __construct()
    {
        $this->dataTypeHelper = new DataTypeHelper();
    }

    /**
     * Describes model
     *
     * @param string $modelType
     * @return array
     * @codeCoverageIgnore
     */
    public function describeModel(string $modelType): array
    {
        $modelClass = str_replace('[]', '', $modelType);
        $modelName = $this->dataTypeHelper->getDataType($modelType);
        $describedModels = [$modelName => []];

        try {
            $reflectionClass = new \ReflectionClass($modelClass);
            $classProperties = $reflectionClass->getProperties();

            foreach ($classProperties as $classProperty) {
                if ($classProperty->isPublic()) {
                    $propertyDoc = $classProperty->getDocComment();

                    if ($propertyDoc) {
                        $propertyName = $this->getPropertyName($classProperty, $propertyDoc);
                        $propertyClass = $this->getPropertyType($propertyDoc);
                        $propertyType = $this->dataTypeHelper->getDataType($propertyClass);
                        $propertyOptional = $this->isPropertyOptional($propertyDoc);
                        $isDataTypeModel = $this->dataTypeHelper->isModelType($propertyClass);
                        $isRecurrentModel = ($modelClass === str_replace('[]', '', $propertyClass));

                        if ($isDataTypeModel && !$isRecurrentModel) {
                            $subModel = $this->describeSubModel($propertyClass);
                            foreach ($subModel as $subModelName => $subModelProps) {
                                $describedModels[$subModelName] = $subModelProps;
                            }
                        }

                        $describedModels[$modelName][] = [
                            'propertyName'     => $propertyName,
                            'propertyType'     => $propertyType,
                            'propertyOptional' => $propertyOptional
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $describedModels;
    }

    /**
     * Describes model
     *
     * @param string $modelType
     * @return array
     * @codeCoverageIgnore
     */
    public function describeSubModel(string $modelType): array
    {
        $modelClass = str_replace('[]', '', $modelType);
        $modelName = $this->dataTypeHelper->getSimpleModelName($modelType);
        $describedModels = [$modelName => []];

        try {
            $reflectionClass = new \ReflectionClass($modelClass);
            $classProperties = $reflectionClass->getProperties();

            foreach ($classProperties as $classProperty) {
                if ($classProperty->isPublic()) {
                    $propertyDoc = $classProperty->getDocComment();

                    if ($propertyDoc) {
                        $propertyName = $this->getPropertyName($classProperty, $propertyDoc);
                        $propertyClass = $this->getPropertyType($propertyDoc);
                        $propertyType = $this->dataTypeHelper->getDataType($propertyClass);
                        $propertyOptional = $this->isPropertyOptional($propertyDoc);
                        $propertyHidden = $this->isPropertyHidden($propertyDoc);
                        $isDataTypeModel = $this->dataTypeHelper->isModelType($propertyClass);

                        if ($propertyHidden === false) {
                            if ($isDataTypeModel) {
                                $subModel = $this->describeSubModel($propertyClass);
                                foreach ($subModel as $subModelName => $subModelProps) {
                                    $describedModels[$subModelName] = $subModelProps;
                                }
                            }

                            $describedModels[$modelName][] = [
                                'propertyName'     => $propertyName,
                                'propertyType'     => $propertyType,
                                'propertyOptional' => $propertyOptional
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $describedModels;
    }

    /**
     * Returns property name
     *
     * @param \ReflectionProperty $classProperty
     * @param string $propertyDoc
     * @return string
     */
    private function getPropertyName(\ReflectionProperty $classProperty, string $propertyDoc): string
    {
        $propertyName = $classProperty->getName();
        preg_match('/^[^\*]+\*[^@]+@api:name(.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? $matches[1] : $propertyName;
    }

    /**
     * Returns property name
     *
     * @param string $propertyDoc
     * @return string
     */
    private function getPropertyType(string $propertyDoc): string
    {
        preg_match('/^[^\*]+\*[^@]+@api:type (.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? $matches[1] : 'string';
    }

    /**
     * Returns whether property is hidden
     *
     * @param string $propertyDoc
     * @return bool
     */
    private function isPropertyHidden(string $propertyDoc): bool
    {
        preg_match('/^[^\*]+\*[^@]+@api:hidden(.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? true : false;
    }

    /**
     * Returns whether property is optional
     *
     * @param string $propertyDoc
     * @return bool
     */
    private function isPropertyOptional(string $propertyDoc): bool
    {
        preg_match('/^[^\*]+\*[^@]+@api:optional(.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? true : false;
    }
}
