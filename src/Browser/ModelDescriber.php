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


class ModelDescriber
{
    /**
     * ModelDescriber constructor.
     */
    public function __construct()
    {
    }

    /**
     * Describes model
     *
     * @param string $modelType
     * @param bool $showAsSimpleModel
     * @return array
     * @codeCoverageIgnore
     */
    public function describe(string $modelType, bool $showAsSimpleModel = false): array
    {
        $modelClass = \str_replace('[]', '', $modelType);
        $modelName = $this->getModelName($modelType, $showAsSimpleModel);
        $describedModels = [$modelName => []];

        try {
            $reflectionClass = new \ReflectionClass($modelClass);
            $classProperties = $reflectionClass->getProperties();

            foreach ($classProperties as $classProperty) {
                if ($classProperty->isPublic()) {
                    $propertyDoc = $classProperty->getDocComment();

                    if ($propertyDoc) {
                        $propertyName = $this->getPropertyName($classProperty, $propertyDoc);
                        $propertyType = $this->getPropertyDataType($propertyDoc);
                        $propertyDataType = $this->getPropertySimpleType($propertyType);
                        $propertyOptional = $this->isPropertyOptional($propertyDoc);

                        $isDataTypeModel = $propertyDataType === 'Model';
                        $isDataTypeArray = $propertyDataType === 'Array&lt;Model&gt;';

                        if ($isDataTypeModel || $isDataTypeArray) {
                            $propertyClass = $propertyType;
                            $propertyClassEx = explode('\\', $propertyClass);

                            $propertyType = array_pop($propertyClassEx);
                            if ($isDataTypeArray) {
                                $propertyType = \sprintf('Array&lt;%s&gt;', \str_replace('[]', '', $propertyType));
                            }

                            $describedSubModel = $this->describe($propertyClass, true);
                            foreach ($describedSubModel as $describedSubModelName => $describedSubModelProps) {
                                $describedModels[$describedSubModelName] = $describedSubModelProps;
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
     * Returns model name
     *
     * @param string $modelType
     * @param bool $showAsSimpleModel
     * @return string
     */
    private function getModelName(string $modelType, bool $showAsSimpleModel): string
    {
        $modelTypeExploded = explode('\\', $modelType);
        $modelName = array_pop($modelTypeExploded);

        if ($showAsSimpleModel) {
            return \str_replace('[]', '', $modelName);
        }

        $isModelSimpleTypeArrayOfModels = $this->getPropertySimpleType($modelType) === 'Array&lt;Model&gt;';
        return $isModelSimpleTypeArrayOfModels ? \sprintf('Array&lt;%s&gt;', \str_replace('[]', '', $modelName)) : $modelName;
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
        \preg_match('/^[^\*]+\*[^@]+@api:name(.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? $matches[1] : $propertyName;
    }

    /**
     * Returns property name
     *
     * @param string $propertyDoc
     * @return string
     */
    private function getPropertyDataType(string $propertyDoc): string
    {
        \preg_match('/^[^\*]+\*[^@]+@api:type (.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? $matches[1] : 'string';
    }

    /**
     * Returns property simple type
     *
     * @param string $propertyType
     * @return string
     * @codeCoverageIgnore
     */
    private function getPropertySimpleType(string $propertyType): string
    {
        switch ($propertyType) {
            case 'int':
            case 'integer':
            case 'double':
            case 'float':
            case 'bool':
            case 'boolean':
            case 'string':
            case 'array':
            case 'any':
                return $propertyType;
                break;
            case 'int[]':
            case 'integer[]':
            case 'double[]':
            case 'float[]':
            case 'bool[]':
            case 'boolean[]':
            case 'string[]':
            case 'array[]':
            case 'any[]':
                return \sprintf('Array&lt;%s&gt;', \str_replace('[]', '', $propertyType));
                break;
            default:
                return \stripos($propertyType, '[]') !== false ? 'Array&lt;Model&gt;' : 'Model';
                break;
        }
    }


    /**
     * Returns whether property is optional
     *
     * @param string $propertyDoc
     * @return bool
     */
    private function isPropertyOptional(string $propertyDoc): bool
    {
        \preg_match('/^[^\*]+\*[^@]+@api:optional(.*?)[\r\n]?$/mi', $propertyDoc, $matches);
        return isset($matches[1]) ? true : false;
    }
}
