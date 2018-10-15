<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace Shared;

use MS\RestServer\Shared\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{

    public function setUp()
    {
    }

    public function testCreateClass()
    {
        $headers = new Headers();
        $this->assertInstanceOf('MS\RestServer\Shared\Headers', $headers);
        $this->assertEmpty($headers->getHeaders());

        $headers = new Headers([['name' => 'Content-Type', 'value' => 'application/json'],]);
        $expected = ['Content-Type' => 'application/json'];
        $this->assertInstanceOf('MS\RestServer\Shared\Headers', $headers);
        $this->assertEquals($expected, $headers->getHeaders());

        return $headers;
    }

    /**
     * @param Headers $headers
     * @return Headers
     * @depends testCreateClass
     */
    public function testAddHeaders(Headers $headers)
    {
        $newHeaders = ['name' => 'Accept', 'value' => 'application/json'];
        $expected = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        $headers->addHeaders($newHeaders);
        $this->assertEquals($expected, $headers->getHeaders());

        $newHeaders = [
            ['name' => 'Access-Control-Allow-Origin', 'value' => '*'],
            ['name' => 'Access-Control-Allow-Headers', 'value' => '*'],
        ];
        $expected = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
        ];
        $headers->addHeaders($newHeaders);
        $this->assertEquals($expected, $headers->getHeaders());

        return $headers;
    }

    /**
     * @param Headers $headers
     * @return Headers
     * @depends testAddHeaders
     */
    public function testReplaceHeaders(Headers $headers)
    {
        $replaceHeaders = [
            ['name' => 'Content-Type', 'value' => 'text/html'],
        ];
        $expected = [
            'Content-Type' => 'text/html',
            'Accept' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
        ];
        $headers->addHeaders($replaceHeaders);
        $this->assertEquals($expected, $headers->getHeaders());

        return $headers;
    }

    /**
     * @param Headers $headers
     * @return Headers
     * @depends testReplaceHeaders
     */
    public function testRemoveHeaders(Headers $headers)
    {
        $remove = 'Access-Control-Allow-Headers';
        $expected = [
            'Content-Type' => 'text/html',
            'Accept' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ];
        $headers->removeHeaders($remove);
        $this->assertEquals($expected, $headers->getHeaders());

        $remove = ['Access-Control-Allow-Origin', 'Accept'];
        $expected = ['Content-Type' => 'text/html'];
        $headers->removeHeaders($remove);
        $this->assertEquals($expected, $headers->getHeaders());

        return $headers;
    }

    /**
     * @param Headers $headers
     * @depends testRemoveHeaders
     */
    public function testClearHeaders(Headers $headers)
    {
        $headers->clearHeaders();
        $this->assertEmpty($headers->getHeaders());
    }
}
