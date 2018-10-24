<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

use MS\RestServer\Server\Tools\Obfuscator;
use PHPUnit\Framework\TestCase;

class ObfuscatorTest extends TestCase
{
    /**
     * @var Obfuscator
     */
    private $obfuscator;

    public function setUp()
    {
        $this->obfuscator = new Obfuscator();
    }

    public function testObfuscate()
    {
        $expected = '3TjdIin-kYM-hSxXV2240-00LGe83ufr1M9gXmi1NGZR7CtIRFcszqVI';
        $this->assertEquals($expected, $this->obfuscator->obfuscate(10, 'table', 5));
    }

    public function testDeobfuscateSuccess()
    {
        $obfuscated = '3TjdIin-kYM-hSxXV2240-00LGe83ufr1M9gXmi1NGZR7CtIRFcszqVI';
        $this->assertEquals(10, $this->obfuscator->deobfuscate($obfuscated, 'table', 5));
    }

    public function testDeobfuscateFail()
    {
        $obfuscated = '3TjdIin-kYM-hSxXV2240-00LGe83ufr1M9gXmi1NGZR7CtIRFcszqVI';
        $this->assertEquals($obfuscated, $this->obfuscator->deobfuscate($obfuscated, 'table', 1));
    }
}
