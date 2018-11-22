<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Localization\LocalizationService;
use PHPUnit\Framework\TestCase;


class LocalizationServiceTest extends TestCase
{
    /**
     * @var LocalizationService
     */
    private $localizationService;

    public function setUp()
    {
        try {
            $this->localizationService = LocalizationService::getInstance();
        } catch (\Exception $e) {
        }
    }

    public function tearDown()
    {
    }

    public function testClone()
    {
        $this->expectExceptionMessage('Clone of Config is not allowed.');
        $tmp = clone($this->localizationService);
        unset($tmp);
    }

    public function testText()
    {
        $expected = 'Nie znaleziono klasy kontrolera';
        $this->assertEquals($expected, $this->localizationService->text('serverErrors.99995001'));

        $expected = 'Missing translation for xxx.yyy';
        $this->assertEquals($expected, $this->localizationService->text('xxx.yyy'));
    }
}
