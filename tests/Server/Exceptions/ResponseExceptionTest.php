<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Server\Exceptions;

use MS\RestServer\Server\Errors\ServerErrors;
use MS\RestServer\Server\Exceptions\ResponseException;
use MS\RestServer\Server\Localization\LocalizationService;
use MS\RestServer\Server\Models\ErrorModel;
use PHPUnit\Framework\TestCase;

class ResponseExceptionTest extends TestCase
{
    /**
     * @throws ResponseException
     */
    public function testExceptionCode()
    {
        $this->expectExceptionCode(500);
        throw new ResponseException(500);
    }

    public function testGetErrors()
    {
        $localizationService = LocalizationService::getInstance();

        $errorC = ServerErrors::CONTROLLER_NOT_FOUND_CODE;
        $errorM = $localizationService->text(sprintf('serverErrors.%s', $errorC));
        $errorF = 'field';

        $exception = new ResponseException(500);
        $exception->addError(new ErrorModel($errorC, $errorM, $errorF));
        $this->assertEquals(
            [['code' => $errorC, 'message' => $errorM, 'field' => $errorF]],
            $exception->getErrors()
        );
    }
}
