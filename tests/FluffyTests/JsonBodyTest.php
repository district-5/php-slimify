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
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slimify\SlimifyInstance;
use SlimifyTests\TestAbstract;

/**
 * Class ArgumentsAndParamsTest
 * @package SlimifyTests\FluffyTests
 */
class JsonBodyTest extends TestAbstract
{
    public function testJsonParsedAsArray()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{"foo":"bar","baz":"qux","page":1}');
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

        $this->assertFalse($instance->isAjaxRequest());
        $this->assertEquals('bar', $instance->getQueryParam('foo'));
        $this->assertEquals('DEFAULT', $instance->getQueryParam('notfound', 'DEFAULT'));

        $this->assertEquals(123, $instance->getPageNumber('page'));
        $this->assertEquals(1, $instance->getPageNumber('notfound'));
        $this->assertEquals(123, $instance->getQueryParam('page'));

        $this->assertNull($instance->getQueryParamInt('float-page'));

        $this->assertNull($instance->getQueryParamInt('overflow-page'));

        $jsonBody = $instance->getJsonBody();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);

        $jsonBody = $instance->getJsonBody();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);

        $jsonBody = $instance->getJsonBodyArray();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);

        $jsonBody = $instance->getJsonBodyArray();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);
    }

    public function testJsonParsedAsObject()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{"foo":"bar","baz":"qux","page":1}');
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

        $this->assertFalse($instance->isAjaxRequest());
        $this->assertEquals('bar', $instance->getQueryParam('foo'));
        $this->assertEquals('DEFAULT', $instance->getQueryParam('notfound', 'DEFAULT'));

        $this->assertEquals(123, $instance->getPageNumber('page'));
        $this->assertEquals(1, $instance->getPageNumber('notfound'));
        $this->assertEquals(123, $instance->getQueryParam('page'));

        $this->assertNull($instance->getQueryParamInt('float-page'));

        $this->assertNull($instance->getQueryParamInt('overflow-page'));

        $jsonBody = $instance->getJsonBody(false);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);

        $jsonBody = $instance->getJsonBody(false);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);

        $jsonBody = $instance->getJsonBodyAsObject();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);

        $jsonBody = $instance->getJsonBodyAsObject();
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);
    }

    public function testJsonParsed()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{"foo":"bar","baz":"qux","page":1}');
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

        $this->assertFalse($instance->isAjaxRequest());
        $this->assertEquals('bar', $instance->getQueryParam('foo'));
        $this->assertEquals('DEFAULT', $instance->getQueryParam('notfound', 'DEFAULT'));

        $this->assertEquals(123, $instance->getPageNumber('page'));
        $this->assertEquals(1, $instance->getPageNumber('notfound'));
        $this->assertEquals(123, $instance->getQueryParam('page'));

        $this->assertNull($instance->getQueryParamInt('float-page'));

        $this->assertNull($instance->getQueryParamInt('overflow-page'));

        $jsonBody = $instance->getJsonBody(true);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);

        $jsonBody = $instance->getJsonBody(true);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody['foo']);
        $this->assertEquals('qux', $jsonBody['baz']);
        $this->assertEquals(1, $jsonBody['page']);

        $jsonBody = $instance->getJsonBody(false);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);

        $jsonBody = $instance->getJsonBody(false);
        $this->assertNotEmpty($jsonBody);
        // '{"foo":"bar","baz":"qux","page":1}'
        $this->assertEquals('bar', $jsonBody->foo);
        $this->assertEquals('qux', $jsonBody->baz);
        $this->assertEquals(1, $jsonBody->page);
    }

    public function testJsonParsedInvalid()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{\"json\":{\"username\":\"1062576\",\"accountId\":\"45656565\"}]}');
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

        $this->assertNull($instance->getJsonBody(true));
        $this->assertNull($instance->getJsonBody(false));
        $this->assertNull($instance->getJsonBodyArray());
        $this->assertNull($instance->getJsonBodyAsObject());
    }
}
