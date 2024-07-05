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
 * Class PostParamsTest
 * @package SlimifyTests
 */
class PostParamsTest extends TestAbstract
{
    public function testPostParams()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $fakeRequestBody = fopen('php://temp', 'r+');
        fwrite($fakeRequestBody, 'foo=bar&baz=qux');
        fseek($fakeRequestBody, 0);

        $stream = new Stream($fakeRequestBody);
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
        $newRequest = $newRequest->withParsedBody(['foo' => 'bar', 'baz' => 'qux', 'page' => 1]);

        $instance->setInterfaces(
            $newRequest,
            new Response()
        );

        $this->assertInstanceOf(Request::class, $instance->request());

        $this->assertNotEmpty($instance->getAllPostParams());
        $this->assertEquals('foo', $instance->getPostParam('notfound', 'foo'));
        $this->assertEquals('bar', $instance->getPostParam('foo'));
        $this->assertEquals('qux', $instance->getPostParam('baz'));
        $this->assertEquals('1', $instance->getPostParam('page'));
        $this->assertEquals(1, $instance->getPostParamInt('page'));
    }

    public function testPostParamsOtherWay()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container);
        $instance->setDevelopment(true);

        $fakeRequestBody = fopen('php://temp', 'r+');
        fwrite($fakeRequestBody, 'foo=bar&baz=qux');
        fseek($fakeRequestBody, 0);

        $stream = new Stream($fakeRequestBody);
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
        $newRequest = $newRequest->withParsedBody(['foo' => 'bar', 'baz' => 'qux', 'page' => 1]);

        $instance->setInterfaces(
            $newRequest,
            new Response()
        );

        $this->assertInstanceOf(Request::class, $instance->request());
        $this->assertEquals('foo', $instance->getPostParam('notfound', 'foo'));
        $this->assertEquals('bar', $instance->getPostParam('foo'));
        $this->assertEquals('qux', $instance->getPostParam('baz'));
        $this->assertEquals('1', $instance->getPostParam('page'));
        $this->assertEquals(1, $instance->getPostParamInt('page'));

        $this->assertNotEmpty($instance->getAllPostParams());
    }
}
