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

use MS\Json\Utils\Utils;
use MS\RestServer\Server\Models\AbstractModel;
use MS\RestServer\Server\Request;


/**
 * @codeCoverageIgnore
 */
class InputBodyValidator
{
    /**
     * @var Utils
     */
    private $utils;
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
     * InputBodyValidator constructor.
     *
     * @param Request $request
     * @param array $inputParams
     */
    public function __construct(Request $request, array $inputParams)
    {
        $this->utils = new Utils();
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

        return $this->validateType($this->inputParams['body'][0]['paramType'], $this->requestBody);
    }

    /**
     * Validates variable type
     *
     * @param string $variableType
     * @param $variableValue
     * @return array
     */
    private function validateType(string $variableType, $variableValue): array
    {
        switch ($variableType) {
            case 'any':
            case 'any[]':
                return $this->validateAny($variableType, $variableValue);
            case 'int':
            case 'integer':
            case 'int[]':
            case 'integer[]':
                return $this->validateInteger($variableType, $variableValue);
            case 'double':
            case 'double[]':
                return $this->validateDouble($variableType, $variableValue);
            case 'float':
            case 'float[]':
                return $this->validateFloat($variableType, $variableValue);
            case 'bool':
            case 'boolean':
            case 'bool[]':
            case 'boolean[]':
                return $this->validateBoolean($variableType, $variableValue);
            case 'string':
            case 'string[]':
                return $this->validateString($variableType, $variableValue);
            case 'array':
            case 'array[]':
                return $this->validateArray($variableType, $variableValue);
            default:
                return $this->validateModel($variableType, $variableValue);
        }
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateAny(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica zmiennych';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if ($value === 'null') {
                    $errors['body'][$index] = 'Wymagana zmienna';
                }
            }
            return $errors;
        }

        if ($paramValue === 'null') {
            $errors['body'] = 'Wymagana zmienna';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateInteger(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica liczb całkowitych';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!is_numeric($value) && !is_int($value)) {
                    $errors['body'][$index] = 'Wymagana liczba całkowita';
                }
            }
            return $errors;
        }

        if (!is_numeric($paramValue) && !is_int($paramValue)) {
            $errors['body'] = 'Wymagana liczba całkowita';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateDouble(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!is_numeric($value) && !is_double($value)) {
                    $errors['body'][$index] = 'Wymagana liczba zmiennoprzecinkowa';
                }
            }
            return $errors;
        }

        if (!is_numeric($paramValue) && !is_double($paramValue)) {
            $errors['body'] = 'Wymagana liczba zmiennoprzecinkowa';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateFloat(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!is_numeric($value) && !is_float($value)) {
                    $errors['body'][$index] = 'Wymagana liczba zmiennoprzecinkowa';
                }
            }
            return $errors;
        }

        if (!is_numeric($paramValue) && !is_float($paramValue)) {
            $errors['body'] = 'Wymagana liczba zmiennoprzecinkowa';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateBoolean(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica zmiennych logicznych';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!is_bool($value)) {
                    $errors['body'][$index] = 'Wymagana zmienna logiczna';
                }
            }
            return $errors;
        }

        if (!is_bool($paramValue)) {
            $errors['body'] = 'Wymagana zmienna logiczna';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateString(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica ciągów znaków';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!is_string($value)) {
                    $errors['body'][$index] = 'Wymagany ciąg znaków';
                }
            }
            return $errors;
        }

        if (!is_string($paramValue)) {
            $errors['body'] = 'Wymagany ciąg znaków';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateArray(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = 'Wymagana tablica tablic';
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                if (!$this->isValidArray($value)) {
                    $errors['body'][$index] = 'Wymagana tablica';
                }
            }
            return $errors;
        }

        if (!$this->isValidArray($paramValue)) {
            $errors['body'] = 'Wymagana tablica';
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @param string $paramType
     * @return array
     */
    private function validateModel(string $paramType, $paramValue): array
    {
        $errors = [];

        if (stripos($paramType, '[]') !== false) {
            $modelName = str_replace('[]', '', $paramType);

            if (!$this->isValidArray($paramValue)) {
                $errors['body'] = \sprintf('Wymagana tablica %s', $modelName);
                return $errors;
            }
            foreach ($paramValue as $index => $value) {
                /**
                 * @var $tmpModel AbstractModel
                 */
                $tmpModel = new $modelName($value);
                $validationErrors = $tmpModel->validate();
                foreach ($validationErrors as $propName => $propError) {
                    $errors['body'][$index][$propName] = $propError;
                }
            }
            return $errors;
        }

        /**
         * @var $tmpModel AbstractModel
         */
        $tmpModel = new $paramType($paramValue);
        $validationErrors = $tmpModel->validate();
        foreach ($validationErrors as $propName => $propError) {
            $errors['body'][$propName] = $propError;
        }
        return $errors;
    }

    /**
     * @param $paramValue
     * @return bool
     */
    private function isValidArray($paramValue): bool
    {
        if (!is_array($paramValue)) {
            return false;
        }
        $arrayKeys = \array_keys($paramValue);
        foreach ($arrayKeys as $key) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }
}
