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
            if (!$validator->validate($this->requestBody)) {
                return $validator->getErrors('body');
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
         * @var AbstractSimpleTypeValidator $validator
         */
        $validator = new $validatorClass();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors( 'body', $paramType);
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
        $validator = new SimpleArrayValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors( 'body', $paramType);
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
            return $validator->getErrors($paramValue, 'body', $validatorType);
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
        $paramValue = $this->requestBody;
        $modelClass = $paramData['paramType'];
        $modelName = explode('\\', $modelClass);
        $modelName = array_pop($modelName);
        $errors = [];

        $validator = new SimpleObjectValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors('body', $modelName);
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

        $validator = new SimpleArrayValidator();
        if (!$validator->validate($paramValue)) {
            return $validator->getErrors('body', $modelName);
        }

        $errors = [];
        $modelName = str_replace('[]', '', $modelName);

        foreach ($paramValue as $index => $value) {
            $validator = new SimpleObjectValidator();
            if (!$validator->validate($value)) {
                $errors = array_merge(
                    $errors,
                    $validator->getErrors(sprintf('body.%s', $index), $modelName)
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
