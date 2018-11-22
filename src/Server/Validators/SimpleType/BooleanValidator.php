<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Validators\SimpleType;

use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;
use MS\RestServer\Server\Validators\Interfaces\SimpleTypeValidator;


class BooleanValidator implements SimpleTypeValidator
{
    /**
     * Validates value

     * @param $value
     * @param string $requiredType
     * @return ErrorModel|null
     */
    public function validate($value, $requiredType = 'boolean'): ?ErrorModel
    {
        if (!is_bool($value)) {
            $localizationService = LocalizationService::getInstance();
            $errorC = ServerErrors::TYPE_REQUIRED;
            $errorM = $localizationService->text(sprintf('serverErrors.%s', $errorC));
            $errorM = sprintf($errorM, $requiredType);

            return new ErrorModel($errorC, $errorM);
        }
        return null;
    }
}
