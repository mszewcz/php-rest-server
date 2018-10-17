<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Helpers\DataTypeHelper;
use PHPUnit\Framework\TestCase;

class DataTypeHelperTest extends TestCase
{
    /**
     * @var DataTypeHelper
     */
    private $dataTypeHelper;

    public function setUp()
    {
        $this->dataTypeHelper = new DataTypeHelper();
    }

    public function testGetDataType()
    {
        $this->assertEquals('string', $this->dataTypeHelper->getDataType('string'));
        $this->assertEquals('Array&lt;string&gt;', $this->dataTypeHelper->getDataType('string[]'));
        $this->assertEquals('Model', $this->dataTypeHelper->getDataType('\\Test\\Model'));
        $this->assertEquals('Array&lt;Model&gt;', $this->dataTypeHelper->getDataType('\\Test\\Model[]'));
    }

    public function testIsModelType()
    {
        $this->assertEquals(false, $this->dataTypeHelper->isModelType('string'));
        $this->assertEquals(false, $this->dataTypeHelper->isModelType('string[]'));
        $this->assertEquals(true, $this->dataTypeHelper->isModelType('\\Test\\Model'));
        $this->assertEquals(true, $this->dataTypeHelper->isModelType('\\Test\\Model[]'));
    }

    public function testGetSimpleModelName()
    {
        $this->assertEquals('Model', $this->dataTypeHelper->getSimpleModelName('\\Test\\Model'));
        $this->assertEquals('Model', $this->dataTypeHelper->getSimpleModelName('\\Test\\Model[]'));
    }
}
