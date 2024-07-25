<?php
/**
 * District5 - GameLeaderboard
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 *
 */

namespace SlimifyTests;

use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slimify\SlimifyCors;

/**
 * Class SlimifyCorsTest
 * @package SlimifyTests
 */
class SlimifyCorsTest extends TestAbstract
{
    public function testCorsGetsAndSets()
    {
        $cors = SlimifyCors::retrieve();
        $this->assertInstanceOf(SlimifyCors::class, $cors);
        $this->assertFalse($cors->hasBeenConfigured());

        $responseHeadersBefore = $cors->getResponseHeaders();
        $this->assertArrayNotHasKey('Access-Control-Allow-Headers', $responseHeadersBefore);
        $this->assertArrayNotHasKey('Access-Control-Allow-Methods', $responseHeadersBefore);
        $this->assertArrayNotHasKey('Access-Control-Allow-Credentials', $responseHeadersBefore);
        $this->assertArrayNotHasKey('Access-Control-Expose-Headers', $responseHeadersBefore);
        $this->assertArrayNotHasKey('Access-Control-Max-Age', $responseHeadersBefore);
        $this->assertEquals('*', $cors->getResponseHeaders()['Access-Control-Allow-Origin']);

        $cors->setAllowedHeaders(['X-Test-Header']);
        $this->assertEquals(['X-Test-Header'], $cors->getAllowedHeaders());

        $cors->setAllowedMethods(['GET', 'POST']);
        $this->assertEquals(['GET', 'POST'], $cors->getAllowedMethods());

        $cors->setAllowedOrigins(['http://localhost']);
        $this->assertEquals(['http://localhost'], $cors->getAllowedOrigins());

        $cors->setAllowCredentials(true);
        $this->assertTrue($cors->getAllowCredentials());

        $cors->setExposedHeaders(['X-Test-Header']);
        $this->assertEquals(['X-Test-Header'], $cors->getExposedHeaders());

        $cors->setMaxAge(3600);
        $this->assertEquals(3600, $cors->getMaxAge());

        $responseHeadersAfter = $cors->getResponseHeaders();
        $this->assertArrayHasKey('Access-Control-Allow-Headers', $responseHeadersAfter);
        $this->assertArrayHasKey('Access-Control-Allow-Methods', $responseHeadersAfter);
        $this->assertArrayHasKey('Access-Control-Allow-Credentials', $responseHeadersAfter);
        $this->assertArrayHasKey('Access-Control-Expose-Headers', $responseHeadersAfter);
        $this->assertArrayHasKey('Access-Control-Max-Age', $responseHeadersAfter);
        $this->assertEquals('http://localhost', $cors->getResponseHeaders()['Access-Control-Allow-Origin']);
        $this->assertEquals('X-Test-Header', $cors->getResponseHeaders()['Access-Control-Allow-Headers']);
        $this->assertEquals('GET, POST', $cors->getResponseHeaders()['Access-Control-Allow-Methods']);
        $this->assertEquals('true', $cors->getResponseHeaders()['Access-Control-Allow-Credentials']);
        $this->assertEquals('X-Test-Header', $cors->getResponseHeaders()['Access-Control-Expose-Headers']);
    }

    public function testHeadersWhenOriginFound()
    {
        $request = new Request(
            'POST',
            new Uri('https', 'example.com', 443, '/test', 'foo=bar&baz=qux&page=123&float-page=1.234&overflow-page=02'),
            new Headers(['Origin' => 'https://example.com']),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY'],
            new Stream(fopen('php://temp', 'r+')),
        );
        $cors = SlimifyCors::retrieve();
        $cors->reset();

        $cors->setAllowedOrigins(['https://example.com', 'https://example2.com']);

        $responseHeaders = $cors->getResponseHeaders($request->getHeaderLine('Origin'));
        $this->assertEquals('https://example.com', $responseHeaders['Access-Control-Allow-Origin']);

        $newRequest = new Request(
            'POST',
            new Uri('https', 'example2.com', 443, '/test', 'foo=bar&baz=qux&page=123&float-page=1.234&overflow-page=02'),
            new Headers(['Origin' => 'https://example2.com']),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY'],
            new Stream(fopen('php://temp', 'r+')),
        );

        $responseHeaders = $cors->getResponseHeaders($newRequest->getHeaderLine('Origin'));
        $this->assertEquals('https://example2.com', $responseHeaders['Access-Control-Allow-Origin']);
    }
}
