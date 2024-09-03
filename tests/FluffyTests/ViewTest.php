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
 * Class ViewTest
 * @package SlimifyTests\FluffyTests
 */
class ViewTest extends TestAbstract
{
    public function testViewWithLayout()
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

        $this->assertNull($instance->getView('test'));

        $instance->addView(realpath(__DIR__ . '/../view'), 'testlayout.phtml', ['static' => 'Foo'], 'test');
        $view = $instance->getView('test');
        $this->assertNotNull($view);
        $returned = $view->render(
            new Response(),
            'testview.phtml',
            ['name' => 'Joe']
        );

        $this->assertEquals(
            '<html><p>Foo</p><b>Joe</b></html>',
            $returned->getBody()->__toString()
        );
    }


    public function testViewWithoutLayout()
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

        $this->assertNull($instance->getView('test'));

        $instance->addView(realpath(__DIR__ . '/../view'), null, ['static' => 'Foo'], 'test');
        $view = $instance->getView('test');
        $this->assertNotNull($view);
        $returned = $view->render(
            new Response(),
            'testview.phtml',
            ['name' => 'Joe']
        );

        $this->assertEquals(
            '<b>Joe</b>',
            $returned->getBody()->__toString()
        );
    }
}
