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

namespace SlimifyTests\FluffyTests;

use DI\Container;
use Exception;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slimify\Helper\Exception\InvalidJsonRequestException;
use Slimify\SlimifyInstance;
use SlimifyTests\TestAbstract;

/**
 * Class ArgumentsAndParamsTest
 * @package SlimifyTests\FluffyTests
 */
class ParsedJsonBodyTest extends TestAbstract
{
    /**
     * @return void
     */
    public function testInvalidRequest()
    {
        $this->expectException(InvalidJsonRequestException::class);
        $instance = $this->getSlimifyInstance(false);
        $instance->getJsonParser();
        $this->fail('Expected exception not thrown');
    }

    /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testGenericAny()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            'bar',
            $parser->anything('foo')
        );

        $this->assertEquals(
            'qux',
            $parser->anything('baz')
        );

        try {
            $parser->anything('not-foo');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }

        $this->assertEquals(
            'bar',
            $parser->anything('not-foo', false, 'bar')
        );
    }

        /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testInt()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            1,
            $parser->int('page')
        );
        try {
            $parser->int('not-page');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertEquals(
            1,
            $parser->int('page-str')
        );
        $this->assertNull($parser->int('not-page', false, null));
        try {
            $parser->int('foo');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
    }

        /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testString()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            'bar',
            $parser->string('foo')
        );
        try {
            $parser->string('not-foo');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertEquals('999', $parser->string('not-foo', false, '999'));
        try {
            $this->assertEquals('999', $parser->string('page', true, '999'));
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertEquals('999', $parser->string('page', false, '999'));
    }

        /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testBool()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertTrue(
            $parser->bool('bool')
        );
        try {
            $parser->bool('not-bool');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertFalse(
            $parser->bool('bool-str')
        );
        $this->assertFalse(
            $parser->bool('bool-int-str')
        );

        $this->assertTrue(
            $parser->bool('bool-yn')
        );
        try {
            $parser->bool('not-bool-yn');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        try {
            $parser->bool('foo', true, false);
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertFalse(
            $parser->bool('foo', false, false)
        );
        $this->assertTrue(
            $parser->bool('foo', false, true)
        );

        $this->assertTrue(
            $parser->bool('bool-int')
        );
        try {
            $parser->bool('not-bool-int');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
    }

    /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testFloat()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            1.234,
            $parser->float('float-page')
        );
        try {
            $parser->float('not-float-page');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertEquals(
            1.234,
            $parser->float('float-page-str')
        );
        $this->assertNull(
            $parser->float('not-float-page', false, null)
        );
        try {
            $parser->float('foo', true, null);
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
    }

    /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testArray()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            ['a', 'b'],
            $parser->array('array')
        );
        try {
            $parser->array('not-array');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertNull(
            $parser->array('not-array', false, null)
        );
        try {
            $parser->array('overflow-page', true, null);
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
    }

    /**
     * @return void
     * @throws InvalidJsonRequestException
     */
    public function testMongoId()
    {
        $instance = $this->getSlimifyInstance();
        $parser = $instance->getJsonParser();

        $this->assertEquals(
            '66b5b160f367a8e098006a0f',
            $parser->mongoId('mongoId')
        );
        try {
            $parser->mongoId('not-mongoId');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
        $this->assertNull(
            $parser->mongoId('foo', false, null)
        );
        try {
            $parser->mongoId('foo', true, null);
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidJsonRequestException::class, $e);
        }
    }

    /**
     * @param bool $valid
     * @return SlimifyInstance
     */
    private function getSlimifyInstance(bool $valid = true): SlimifyInstance
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));
        if ($valid === true) {
            $stream->write('{"foo":"bar","baz":"qux","page":1,"page-str":"1","bool":true,"bool-str":"false","bool-yn":"yes","bool-int":1,"bool-int-str":"0","float-page":1.234,"float-page-str":"1.234","overflow-page":"02","array":["a","b"],"mongoId":"66b5b160f367a8e098006a0f"}');
        } else {
            $stream->write('{"foo""bar","baz":"qux","page":"1","bool":"true","float-page":"1.234","overflow-page":"02","array":["a","b"],"mongoId":"66b5b160f367a8e098006a0f"}');
        }
        $stream->rewind();

        $request = new Request(
            'POST',
            new Uri('https', 'example.com', 443, '/test', 'foo=bar&baz=qux&page=123&float-page=1.234&overflow-page=02'),
            new Headers(['X-Foo' => ['Bar']]),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY'],
            $stream,
        );
        $newRequest = $request->withBody($stream);

        $instance->setInterfaces(
            $newRequest,
            new Response()
        );

        return $instance;
    }
}
