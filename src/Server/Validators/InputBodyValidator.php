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
        $errors = [];

        switch ($variableType) {
            case 'any':
                if ($variableValue === 'null') {
                    $errors['body'] = 'Wymagana zmienna';
                }
                break;
            case 'any[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica zmiennych';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica zmiennych';
                    }
                    if ($bodyItem === 'null') {
                        $errors['body'][$index] = 'Wymagana zmienna';
                    }
                }
                break;
            case 'int':
            case 'integer':
                if (!is_numeric($variableValue) && !is_int($variableValue)) {
                    $errors['body'] = 'Wymagana liczba całkowita';
                }
                break;
            case 'int[]':
            case 'integer[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica liczb całkowitych';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica liczb całkowitych';
                    }
                    if (!is_numeric($bodyItem) && !is_int($bodyItem)) {
                        $errors['body'][$index] = 'Wymagana liczba całkowita';
                    }
                }
                break;
            case 'double':
                if (!is_numeric($variableValue) && !is_double($variableValue)) {
                    $errors['body'] = 'Wymagana liczba zmiennoprzecinkowa';
                }
                break;
            case 'double[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                    }
                    if (!is_numeric($bodyItem) && !is_double($bodyItem)) {
                        $errors['body'][$index] = 'Wymagana liczba zmiennoprzecinkowa';
                    }
                }
                break;
            case 'float':
                if (!is_numeric($variableValue) && !is_float($variableValue)) {
                    $errors['body'] = 'Wymagana liczba zmiennoprzecinkowa';
                }
                break;
            case 'float[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica liczb zmiennoprzecinkowych';
                    }
                    if (!is_numeric($bodyItem) && !is_float($bodyItem)) {
                        $errors['body'][$index] = 'Wymagana liczba zmiennoprzecinkowa';
                    }
                }
                break;
            case 'bool':
            case 'boolean':
                if (!is_bool($variableValue)) {
                    $errors['body'] = 'Wymagana zmienna logiczna';
                }
                break;
            case 'bool[]':
            case 'boolean[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica zmiennych logicznych';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica zmiennych logicznych';
                    }
                    if (!is_bool($bodyItem)) {
                        $errors['body'][$index] = 'Wymagana zmienna logiczna';
                    }
                }
                break;
            case 'string':
                if (!is_string($variableValue)) {
                    $errors['body'] = 'Wymagany ciąg znaków';
                }
                break;
            case 'string[]':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica ciągów znaków';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica ciągów znaków';
                    }
                    if (!is_string($bodyItem) || is_numeric($bodyItem)) {
                        $errors['body'][$index] = 'Wymagany ciąg znaków';
                    }
                }
                break;
            case 'array':
                if (!is_array($variableValue)) {
                    $errors['body'] = 'Wymagana tablica';
                }
                foreach ($variableValue as $index => $bodyItem) {
                    if (!is_int($index)) {
                        $errors['body'] = 'Wymagana tablica';
                    }
                }
                break;
            default:
                if (stripos($variableType, '[]') !== false) {
                    if (!is_array($variableValue)) {
                        $errors['body'] = \sprintf('Wymagana tablica %s', $variableType);
                    }
                    $modelName = str_replace('[]', '', $variableType);
                    foreach ($variableValue as $index => $bodyItem) {
                        if (!is_int($index)) {
                            $errors['body'] = \sprintf('Wymagana tablica %s', $variableType);
                        }
                        if (is_int($index)) {
                            /**
                             * @var $tmpModel AbstractModel
                             */
                            $tmpModel = new $modelName($bodyItem);
                            $validationErrors = $tmpModel->validate();
                            foreach ($validationErrors as $propName => $propError) {
                                $errors['body'][$index][$propName] = $propError;
                            }
                        }
                    }
                }
                if (stripos($variableType, '[]') === false) {
                    /**
                     * @var $tmpModel AbstractModel
                     */
                    $tmpModel = new $variableType($variableValue);
                    $validationErrors = $tmpModel->validate();
                    foreach ($validationErrors as $propName => $propError) {
                        $errors['body'][$propName] = $propError;
                    }
                }
                break;
        }

        return $errors;
    }
}
