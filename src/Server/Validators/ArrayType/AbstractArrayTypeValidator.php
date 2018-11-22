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


abstract class AbstractArrayTypeValidator
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
     * @param array $value
     * @return bool
     */
    public abstract function validate(array $value): bool;

    /**
     * @param array $value
     * @param string $fieldName
     * @param string|null $requiredType
     * @return array
     */
    public abstract function getErrors(array $value, string $fieldName, $requiredType = 'any'): array;

    /**
     * @param string $fieldName
     * @param int $index
     * @param string $requiredType
     * @return ErrorModel
     */
    protected function getErrorModel(string $fieldName, int $index, $requiredType = 'any'): ErrorModel
    {
        $errorC = ServerErrors::TYPE_REQUIRED;
        $errorM = $this->localizationService->text(sprintf('serverErrors.%s', $errorC));
        $errorM = sprintf($errorM, $requiredType);
        $errorF = sprintf('%s.%s', $fieldName, $index);

        return new ErrorModel($errorC, $errorM, $errorF);
    }
}
