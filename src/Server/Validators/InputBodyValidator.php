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

use MS\RestServer\Server\Models\AbstractModel;
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
        if (is_null($this->requestBody) && $this->inputParams['body'][0]['paramRequired'] === true) {
            return ['body' => 'To pole jest wymagane'];
        }

        $variableType = $this->inputParams['body'][0]['paramType'];

        if (\in_array($variableType, $this->simpleTypes)) {
            return $this->validateSimpleType($this->inputParams['body'][0]);
        }
        if (\in_array($variableType, $this->arrayTypes)) {
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

        $validatorClass = \sprintf(
            '\\MS\RestServer\\Server\\Validators\\SimpleType\\%sValidator',
            \ucfirst($paramType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new $validatorClass();
        $result = $validator->validate($paramValue, $paramType);
        if ($result !== null) {
            return ['body' => $result];
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

        $validatorType = \str_replace('[]', '', $paramType);
        $validatorClass = \sprintf(
            '\\MS\RestServer\\Server\\Validators\\ArrayType\\%sValidator',
            \ucfirst($validatorType)
        );
        /**
         * @var SimpleTypeValidator $validator
         */
        $validator = new SimpleArrayValidator();
        $result = $validator->validate($paramValue, $paramType);
        if ($result !== null) {
            return ['body' => $result];
        }

        $errors = [];
        /**
         * @var ArrayTypeValidator $validator
         */
        $validator = new $validatorClass();
        $result = $validator->validate($paramValue, $validatorType);
        foreach ($result as $index => $error) {
            $errors['body'][$index] = $error;
        }
        return $errors;
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
        $result = $validator->validate($paramValue, $modelName);
        if ($result !== null) {
            return ['body' => $result];
        }

        /**
         * @var AbstractModel $tmpModel
         */
        $tmpModel = new $modelClass((array)$paramValue);
        $validationErrors = $tmpModel->validate();
        foreach ($validationErrors as $propName => $propError) {
            $errors['body'][$propName] = $propError;
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
        $result = $validator->validate($paramValue, $modelName);
        if ($result !== null) {
            return ['body' => $result];
        }

        $errors = [];
        $modelName = str_replace('[]', '', $modelName);

        foreach ($paramValue as $index => $value) {
            /**
             * @var SimpleTypeValidator $validator
             */
            $validator = new SimpleObjectValidator();
            $result = $validator->validate($value, $modelName);
            if ($result !== null) {
                $errors['body'][$index] = $result;
            }
            if ($result === null) {
                /**
                 * @var AbstractModel $tmpModel
                 */
                $tmpModel = new $modelClass((array)$value);
                $validationErrors = $tmpModel->validate();
                foreach ($validationErrors as $propName => $propError) {
                    $errors['body'][$index . '.' . $propName] = $propError;
                }
            }
        }

        return $errors;
    }
}
