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
class InputPathValidator
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
    private $pathParams = [];

    /**
     * InputPathValidator constructor.
     *
     * @param Request $request
     * @param array $params
     */
    public function __construct(Request $request, array $params)
    {
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
            $result = $this->validateType($paramData);
            if ($result !== null) {
                $errors['path'][$paramData['paramName']] = $result;
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
        $paramValue = $this->pathParams[$paramName];
        $error = null;

        switch ($paramType) {
            case 'any':
                if ($paramValue === 'null') {
                    $errors['body'] = 'Wymagana zmienna';
                }
                break;
            case 'int':
            case 'integer':
                if (!is_numeric($paramValue) && !is_int($paramValue)) {
                    return 'Wymagana liczba całkowita';
                }
                break;
            case 'double':
                if (!is_numeric($paramValue) && !is_double($paramValue)) {
                    return 'Wymagana liczba zmiennoprzecinkowa';
                }
                break;
            case 'float':
                if (!is_numeric($paramValue) && !is_float($paramValue)) {
                    return 'Wymagana liczba zmiennoprzecinkowa';
                }
                break;
            case 'string':
                if (!is_string($paramValue) || is_numeric($paramValue) || is_bool($paramValue) || $paramValue === 'null') {
                    return 'Wymagany ciąg znaków';
                }
                break;
        }

        return $error;
    }
}
