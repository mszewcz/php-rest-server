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
use MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator;
use MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator;
use MS\RestServer\Server\Validators\SimpleType\ArrayValidator as SimpleArrayValidator;
use MS\RestServer\Server\Validators\SimpleType\ObjectValidator as SimpleObjectValidator;


/**
 * @codeCoverageIgnore
 */
class InputPathValidator
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
    private $pathParams = [];
    /**
     * @var array
     */
    private $simpleTypes = ['any', 'array', 'bool', 'boolean', 'float', 'int', 'integer', 'string'];
    /**
     * @var array
     */
    private $arrayTypes = ['any[]', 'array[]', 'bool[]', 'boolean[]', 'float[]', 'int[]', 'integer[]', 'string[]'];


    /**
     * InputPathValidator constructor.
     *
     * @param Request $request
     * @param array $params
     */
    public function __construct(Request $request, array $params)
    {
        $this->localizationService = LocalizationService::getInstance();
        $this->request = $request;
        $this->params = $params;
        $this->pathParams = $request->getRequestPathParams();
    }

    /**
     * Validates path params
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
        $paramValue = $this->pathParams[$paramName];

        if ($paramRequired) {
            $validator = new RequiredValidator();
            $result = $validator->validate($paramValue, sprintf('path.%s', $paramName));
            if ($result !== null) {
                return [$result];
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
        $paramValue = $this->pathParams[$paramName];

        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\SimpleType\\%sValidator',
            ucfirst($paramType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new $validatorClass();
        $result = $validator->validate($paramValue, $paramType, sprintf('path.%s', $paramName));
        if ($result !== null) {
            return [$result];
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
        $paramValue = $this->pathParams[$paramName];

        $validatorType = str_replace('[]', '', $paramType);
        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\ArrayType\\%sValidator',
            ucfirst($validatorType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleArrayValidator();
        $result = $validator->validate($paramValue, $paramType, sprintf('path.%s', $paramName));
        if ($result !== null) {
            return [$result];
        }

        /**
         * @var ArrayTypeValidator $validator
         */
        $validator = new $validatorClass();
        return $validator->validate($paramValue, $validatorType, sprintf('path.%s', $paramName));
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
        $paramValue = $this->pathParams[$paramName];
        $modelClass = $paramData['paramType'];
        $modelName = explode('\\', $modelClass);
        $modelName = array_pop($modelName);

        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleObjectValidator();
        $result = $validator->validate($paramValue, $modelName, sprintf('path.%s', $paramName));
        if ($result !== null) {
            return [$result];
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
            if (!preg_match('/^path\.'.$paramName.'\./', $fieldName)) {
                $error->setFieldName(sprintf('path.%s.%s', $paramName, $fieldName));
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
        $paramValue = $this->pathParams[$paramName];
        $modelClass = str_replace('[]', '', $paramType);
        $modelName = explode('\\', $paramType);
        $modelName = array_pop($modelName);

        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleArrayValidator();
        $result = $validator->validate($paramValue, $modelName, sprintf('path.%s', $paramName));
        if ($result !== null) {
            return [$result];
        }

        $errors = [];
        $modelName = str_replace('[]', '', $modelName);

        foreach ($paramValue as $index => $value) {
            /**
             * @var SimpleTypeValidator $validator
             */
            $validator = new SimpleObjectValidator();
            $result = $validator->validate($value, $modelName, sprintf('path.%s.%s', $paramName, $index));
            if ($result !== null) {
                $errors[] = $result;
            }
            if ($result === null) {
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
                    if (!preg_match('/^path\.'.$paramName.'\.[0-9]+\./', $fieldName)) {
                        $error->setFieldName(sprintf('path.%s.%s.%s', $paramName, $index, $fieldName));
                    }
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }
}
