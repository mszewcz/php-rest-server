<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators;

use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\AbstractModel;
use MS\RestServer\Server\Models\ErrorModel;
use MS\RestServer\Server\Request;
use MS\RestServer\Server\Validators\ArrayType\AbstractArrayTypeValidator;
use MS\RestServer\Server\Validators\RequiredType\RequiredValidator;
use MS\RestServer\Server\Validators\SimpleType\AbstractSimpleTypeValidator;
use MS\RestServer\Server\Validators\SimpleType\ArrayValidator as SimpleArrayValidator;
use MS\RestServer\Server\Validators\SimpleType\ObjectValidator as SimpleObjectValidator;


/**
 * @codeCoverageIgnore
 */
class InputQueryValidator
{
    /**
     * @var LocalizationService
     */
    private $localizationService;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var array
     */
    private $params = [];
    /**
     * @var array
     */
    private $queryParams = [];
    /**
     * @var array
     */
    private $simpleTypes = ['any', 'array', 'bool', 'boolean', 'float', 'int', 'integer', 'string'];
    /**
     * @var array
     */
    private $arrayTypes = ['any[]', 'array[]', 'bool[]', 'boolean[]', 'float[]', 'int[]', 'integer[]', 'string[]'];

    /**
     * InputQueryValidator constructor.
     *
     * @param Request $request
     * @param array $params
     */
    public function __construct(Request $request, array $params)
    {
        $this->localizationService = LocalizationService::getInstance();
        $this->request = $request;
        $this->params = $params;
        $this->queryParams = $request->getRequestQueryParams();
    }

    /**
     * Validates query params
     *
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        foreach ($this->params as $paramData) {
            $errors = array_merge($errors, $this->validateType($paramData));
        }
        return $errors;
    }

    /**
     * Validates param type
     *
     * @param array $paramData
     * @return array
     */
    private function validateType(array $paramData): array
    {
        $paramName = $paramData['paramName'];
        $paramType = $paramData['paramType'];
        $paramRequired = $paramData['paramRequired'] === true;
        $paramValue = $this->queryParams[$paramName];

        if ($paramRequired) {
            $validator = new RequiredValidator();
            if (!$validator->validate($paramValue)) {
                return $validator->getErrors(sprintf('query.%s', $paramName));
            }
        }
        if (in_array($paramType, $this->simpleTypes)) {
            return $this->validateSimpleType($paramData);
        }
        if (in_array($paramType, $this->arrayTypes)) {
            return $this->validateArrayType($paramData);
        }
        if (stripos($paramType, '[]') !== false) {
            return $this->validateModelArrayType($paramData);
        }
        return $this->validateModelType($paramData);
    }

    /**
     * @param array $paramData
     * @return array
     */
    private function validateSimpleType(array $paramData): array
    {
        $paramName = $paramData['paramName'];
        $paramType = $paramData['paramType'];
        $paramValue = $this->queryParams[$paramName];

        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\SimpleType\\%sValidator',
            ucfirst($paramType)
        );
        /**
         * @var AbstractSimpleTypeValidator $validator
         */
        $validator = new $validatorClass();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors(sprintf('query.%s', $paramName), $paramType);
        }
        return [];
    }

    /**
     * @param array $paramData
     * @return array
     */
    private function validateArrayType(array $paramData): array
    {
        $paramName = $paramData['paramName'];
        $paramType = $paramData['paramType'];
        $paramValue = $this->queryParams[$paramName];

        $validator = new SimpleArrayValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors(sprintf('query.%s', $paramName), $paramType);
        }

        $validatorType = str_replace('[]', '', $paramType);
        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\ArrayType\\%sValidator',
            ucfirst($validatorType)
        );
        /**
         * @var AbstractArrayTypeValidator $validator
         */
        $validator = new $validatorClass();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors($paramValue, sprintf('query.%s', $paramName), $validatorType);
        }
        return [];
    }

    /**
     * Validates model type
     *
     * @param array $paramData
     * @return array
     */
    private function validateModelType(array $paramData): array
    {
        $paramName = $paramData['paramName'];
        $paramValue = $this->queryParams[$paramName];
        $modelClass = $paramData['paramType'];
        $modelName = explode('\\', $modelClass);
        $modelName = array_pop($modelName);

        $validator = new SimpleObjectValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors(sprintf('query.%s', $paramName), $modelName);
        }

        $errors = [];
        /**
         * @var AbstractModel $tmpModel
         */
        $tmpModel = new $modelClass((array)$paramValue);
        $validationErrors = $tmpModel->validate();
        foreach ($validationErrors as $error) {
            /**
             * @var ErrorModel $error
             */
            $fieldName = $error->getFieldName();
            if (!preg_match('/^query\.'.$paramName.'\./', $fieldName)) {
                $error->setFieldName(sprintf('query.%s.%s', $paramName, $fieldName));
            }
            $errors[] = $error;
        }
        return $errors;
    }

    /**
     * Validates model array type
     *
     * @param array $paramData
     * @return array
     */
    private function validateModelArrayType(array $paramData): array
    {
        $paramName = $paramData['paramName'];
        $paramType = $paramData['paramType'];
        $paramValue = $this->queryParams[$paramName];
        $modelClass = str_replace('[]', '', $paramType);
        $modelName = explode('\\', $paramType);
        $modelName = array_pop($modelName);

        $validator = new SimpleArrayValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors(sprintf('query.%s', $paramName), $modelName);
        }

        $errors = [];
        $modelName = str_replace('[]', '', $modelName);

        foreach ($paramValue as $index => $value) {
            $validator = new SimpleObjectValidator();
            if (!$validator->validate($value)) {
                $errors = array_merge(
                    $errors,
                    $validator->getErrors(sprintf('query.%s.%s', $paramName, $index), $modelName)
                );
            } else {
                /**
                 * @var AbstractModel $tmpModel
                 */
                $tmpModel = new $modelClass((array)$value);
                $validationErrors = $tmpModel->validate();
                foreach ($validationErrors as $error) {
                    /**
                     * @var ErrorModel $error
                     */
                    $fieldName = $error->getFieldName();
                    if (!preg_match('/^query\.'.$paramName.'\.[0-9]+\./', $fieldName)) {
                        $error->setFieldName(sprintf('query.%s.%s.%s', $paramName, $index, $fieldName));
                    }
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }
}
