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
class InputBodyValidator
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
    private $inputParams = [];
    /**
     * @var null
     */
    private $requestBody;
    /**
     * @var array
     */
    private $simpleTypes = ['any', 'array', 'bool', 'boolean', 'float', 'int', 'integer', 'string'];
    /**
     * @var array
     */
    private $arrayTypes = ['any[]', 'array[]', 'bool[]', 'boolean[]', 'float[]', 'int[]', 'integer[]', 'string[]'];

    /**
     * InputBodyValidator constructor.
     *
     * @param Request $request
     * @param array $inputParams
     */
    public function __construct(Request $request, array $inputParams)
    {
        $this->localizationService = LocalizationService::getInstance();
        $this->request = $request;
        $this->inputParams = $inputParams;
        $this->requestBody = $request->getRequestBody();
    }

    /**
     * Validates request body
     *
     * @return array
     */
    public function validate(): array
    {
        if ($this->inputParams['body'][0]['paramRequired'] === true) {
            $validator = new RequiredValidator();
            $result = $validator->validate($this->requestBody, 'body');
            if ($result !== null) {
                return [$result];
            }
        }

        $variableType = $this->inputParams['body'][0]['paramType'];

        if (in_array($variableType, $this->simpleTypes)) {
            return $this->validateSimpleType($this->inputParams['body'][0]);
        }
        if (in_array($variableType, $this->arrayTypes)) {
            return $this->validateArrayType($this->inputParams['body'][0]);
        }
        if (stripos($variableType, '[]') !== false) {
            return $this->validateModelArrayType($this->inputParams['body'][0]);
        }
        return $this->validateModelType($this->inputParams['body'][0]);
    }

    /**
     * Validates simple type
     *
     * @param array $paramData
     * @return array
     */
    private function validateSimpleType(array $paramData): array
    {
        $paramType = $paramData['paramType'];
        $paramValue = $this->requestBody;

        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\SimpleType\\%sValidator',
            ucfirst($paramType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new $validatorClass();
        $result = $validator->validate($paramValue, $paramType, 'body');
        if ($result !== null) {
            return [$result];
        }
        return [];
    }

    /**
     * Validates array type
     *
     * @param array $paramData
     * @return array
     */
    private function validateArrayType(array $paramData): array
    {
        $paramType = $paramData['paramType'];
        $paramValue = $this->requestBody;

        $validatorType = str_replace('[]', '', $paramType);
        $validatorClass = sprintf(
            '\\MS\RestServer\\Server\\Validators\\ArrayType\\%sValidator',
            ucfirst($validatorType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleArrayValidator();
        $result = $validator->validate($paramValue, $paramType, 'body');
        if ($result !== null) {
            return [$result];
        }

        /**
         * @var ArrayTypeValidator $validator
         */
        $validator = new $validatorClass();
        return $validator->validate($paramValue, $validatorType, 'body');
    }

    /**
     * Validates model type
     *
     * @param array $paramData
     * @return array
     */
    private function validateModelType(array $paramData): array
    {
        $paramValue = $this->requestBody;
        $modelClass = $paramData['paramType'];
        $modelName = explode('\\', $modelClass);
        $modelName = array_pop($modelName);
        $errors = [];

        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleObjectValidator();
        $result = $validator->validate($paramValue, $modelName, 'body');
        if ($result !== null) {
            return [$result];
        }

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
            if (!preg_match('/^body\./', $fieldName)) {
                $error->setFieldName(sprintf('body.%s', $fieldName));
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
        $paramType = $paramData['paramType'];
        $paramValue = $this->requestBody;
        $modelClass = str_replace('[]', '', $paramType);
        $modelName = explode('\\', $paramType);
        $modelName = array_pop($modelName);

        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleArrayValidator();
        $result = $validator->validate($paramValue, $modelName, 'body');
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
            $result = $validator->validate($value, $modelName, sprintf('body.%s', $index));
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
                    if (!preg_match('/^body\.[0-9]+\./', $fieldName)) {
                        $error->setFieldName(sprintf('body.%s.%s', $index, $fieldName));
                    }
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }
}
