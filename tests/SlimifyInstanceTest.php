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

use DI\Container;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slimify\SlimifyInstance;
use Slimify\SlimifyResponse;

/**
 * Class SlimifyInstanceTest
 * @package SlimifyTests
 */
class SlimifyInstanceTest extends TestAbstract
{
    public function testSlimifyInstance()
    {
        $container = new Container();
        $instance = new SlimifyInstance(
            new ResponseFactory(),
            $container
        );
        $instance->setArguments(['foo' => 'bar', 'baz' => 'qux', 'empty' => '']);
        $this->assertCount(3, $instance->getArguments());
        $this->assertEquals('bar', $instance->getArgument('foo'));
        $this->assertEquals('qux', $instance->getArgument('baz'));
        $this->assertNull($instance->getArgument('not_found'));
        $this->assertEquals('DEFAULT', $instance->getArgument('empty', 'DEFAULT'));

        $instance->setContainer($container);
        $instance->setDevelopment(true);
        $tempFile = sys_get_temp_dir() . '/slimify.' . uniqid() . '.php';
        $this->assertFileDoesNotExist($tempFile);
        touch($tempFile);
        $this->assertFileExists($tempFile);
        $instance->addRouterWithCache($tempFile, true);
        $this->assertInstanceOf(SlimifyInstance::class, $instance);
        $this->assertInstanceOf(Container::class, $instance->getContainer());

        $instance->setInterfaces(
            new Request(
                'GET',
                new Uri('https', 'example.com', 443, '/test'),
                new Headers(['X-Foo' => ['Bar']]),
                ['IS_A_COOKIE' => 'NOT REALLY'],
                ['IS_A_SERVER' => 'NOT REALLY'],
                new Stream(fopen('php://temp', 'r+')),
            ),
            new Response()
        );

        $response = $instance->response();
        $this->assertInstanceOf(SlimifyResponse::class, $response);
        $this->assertTrue($instance->isDevelopment());
        $this->assertFalse($instance->isProduction());
    }
}
