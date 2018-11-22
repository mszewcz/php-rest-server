<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\RequiredType;

use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;


class RequiredValidator
{
    /**
     * Validates value
     *
     * @param $value
     * @return bool
     */
    public function validate($value): bool
    {
        if (is_object($value) || is_array($value)) {
            return true;
        }
        return !is_null($value) && trim((string) $value) !== '';
    }

    /**
     * Returns error
     *
     * @param string $fieldName
     * @return array
     */
    public function getErrors($fieldName = null): array
    {
        $localizationService = LocalizationService::getInstance();
        $errorC = ServerErrors::FIELD_REQUIRED;
        $errorM = $localizationService->text(sprintf('serverErrors.%s', $errorC));

        return [new ErrorModel($errorC, $errorM, $fieldName)];
    }
}
