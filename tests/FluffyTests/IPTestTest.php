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
 * Class IPTestTest
 * @package SlimifyTests\FluffyTests
 */
class IPTestTest extends TestAbstract
{
    public function testHasIP()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container)->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));

        $request = new Request(
            'POST',
            new Uri('https', 'example.com', 443, '/test', 'foo=bar&baz=qux&page=123&float-page=1.234&overflow-page=02'),
            new Headers(['X-Foo' => ['Bar'], 'X-Requested-With' => ['XMLHttpRequest']]),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY', 'REMOTE_ADDR' => '123.123.123.123'],
            $stream,
        );

        $instance->setInterfaces(
            $request,
            new Response()
        );

        $this->assertEquals('123.123.123.123', $instance->getIp());
    }

    public function testDoesNotHaveIP()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setContainer($container)->setDevelopment(true);

        $stream = new Stream(fopen('php://temp', 'r+'));

        $request = new Request(
            'POST',
            new Uri('https', 'example.com', 443, '/test', 'foo=bar&baz=qux&page=123&float-page=1.234&overflow-page=02'),
            new Headers(['X-Foo' => ['Bar'], 'X-Requested-With' => ['XMLHttpRequest']]),
            ['IS_A_COOKIE' => 'NOT REALLY'],
            ['IS_A_SERVER' => 'NOT REALLY'],
            $stream,
        );

        $instance->setInterfaces(
            $request,
            new Response()
        );

        $this->assertNull($instance->getIp());
    }
}
