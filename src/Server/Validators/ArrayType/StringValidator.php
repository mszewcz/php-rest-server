<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\ArrayType;

use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;
use MS\RestServer\Server\Validators\Interfaces\ArrayTypeValidator;


class StringValidator implements ArrayTypeValidator
{
    /**
     * Validates value
     *
     * @param array $value
     * @param string $requiredType
     * @return array|null
     */
    public function validate(array $value, string $requiredType = 'string'): ?array
    {
        $localizationService = LocalizationService::getInstance();
        $errors = [];
        foreach ($value as $key => $val) {
            if (!is_string($val)) {
                $errorC = ServerErrors::TYPE_REQUIRED;
                $errorM = $localizationService->text(sprintf('serverErrors.%s', $errorC));
                $errorM = sprintf($errorM, $requiredType);

                $errors[$key] = new ErrorModel($errorC, $errorM);
            }
        }
        return $errors;
    }
}
