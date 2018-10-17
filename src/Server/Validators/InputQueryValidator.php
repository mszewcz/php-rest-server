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

use MS\RestServer\Server\Request;


/**
 * @codeCoverageIgnore
 */
class InputQueryValidator
{
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
     * InputQueryValidator constructor.
     *
     * @param Request $request
     * @param array $params
     */
    public function __construct(Request $request, array $params)
    {
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
            $result = $this->validateType($paramData);
            if ($result !== null) {
                $errors['query'][$paramData['paramName']] = $result;
            }
        }
        return $errors;
    }

    /**
     * Validates param type
     *
     * @param array $paramData
     * @return string
     */
    private function validateType(array $paramData): ?string
    {
        $paramName = $paramData['paramName'];
        $paramType = $paramData['paramType'];
        $paramRequired = $paramData['paramRequired'] === true;
        $paramValue = $this->queryParams[$paramName];

        if ($paramRequired && is_null($paramValue)) {
            return 'To pole jest wymagane';
        }

        switch ($paramType) {
            case 'any':
                return $this->validateAny($paramValue);
            case 'int':
            case 'integer':
                return $this->validateInt($paramValue);
            case 'double':
                return $this->validateDouble($paramValue);
            case 'float':
                return $this->validateFloat($paramValue);
            case 'string':
                return $this->validateString($paramValue);
            default:
                return null;
        }
    }

    /**
     * @param $paramValue
     * @return null|\string
     */
    private function validateAny($paramValue): ?string
    {
        if ($paramValue === 'null') {
            return 'Wymagana zmienna';
        }
        return null;
    }

    /**
     * @param $paramValue
     * @return null|string
     */
    private function validateInt($paramValue): ?string
    {
        if (!is_numeric($paramValue) && !is_int($paramValue)) {
            return 'Wymagana liczba całkowita';
        }
        return null;
    }

    /**
     * @param $paramValue
     * @return null|string
     */
    private function validateDouble($paramValue): ?string
    {
        if (!is_numeric($paramValue) && !is_double($paramValue)) {
            return 'Wymagana liczba zmiennoprzecinkowa';
        }
        return null;
    }

    /**
     * @param $paramValue
     * @return null|string
     */
    private function validateFloat($paramValue): ?string
    {
        if (!is_numeric($paramValue) && !is_float($paramValue)) {
            return 'Wymagana liczba zmiennoprzecinkowa';
        }
        return null;
    }

    /**
     * @param $paramValue
     * @return null|string
     */
    private function validateString($paramValue): ?string
    {
        if (!is_string($paramValue) || is_numeric($paramValue) || is_bool($paramValue) || $paramValue === 'null') {
            return 'Wymagany ciąg znaków';
        }
        return null;
    }
}
