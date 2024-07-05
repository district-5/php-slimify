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
}
