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


abstract class AbstractSimpleTypeValidator
{
    /**
     * @var LocalizationService
     */
    private $localizationService;

    /**
     * AbstractArrayTypeValidator constructor.
     */
    protected function __construct()
    {
        $this->localizationService = LocalizationService::getInstance();
    }

    /**
     * @param $value
     * @return bool
     */
    public abstract function validate($value): bool;

    /**
     * @param string $fieldName
     * @param string|null $requiredType
     * @return array
     */
    public function getErrors(string $fieldName, $requiredType = 'any'): array
    {
        $errorC = ServerErrors::TYPE_REQUIRED;
        $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));
        $errorM = sprintf($errorM, $requiredType);
        $errorF = $fieldName;

        return [new ErrorModel($errorC, $errorM, $errorF)];
    }
}
