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

use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;


class RequiredValidator
{
    /**
     * Validates value

     * @param $value
     * @param string $fieldName
     * @return ErrorModel|null
     */
    public function validate($value, $fieldName = null): ?ErrorModel
    {
        if (is_null($value) || trim((string) $value) === '') {
            $localizationService = LocalizationService::getInstance();
            $errorC = ServerErrors::FIELD_REQUIRED;
            $errorM = $localizationService->text(sprintf('serverErrors.%s', $errorC));

            return new ErrorModel($errorC, $errorM, $fieldName);
        }
        return null;
    }
}
