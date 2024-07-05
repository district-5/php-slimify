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
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
use Slimify\SlimifyInstance;
use SlimifyTests\TestAbstract;

/**
 * Class LogTest
 * @package SlimifyTests\FluffyTests
 */
class LogTest extends TestAbstract
{
    private string $fileLogPath;

    public function testFileLog()
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
        $this->fileLogPath = sys_get_temp_dir() . '/slimify-' . uniqid() . '.log';
        $instance->addFileLog(
            $this->fileLogPath,
            Level::Warning,
        );
        $logger = $instance->getContainer()->get('logger');
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $logger = $instance->log();
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);
        $this->assertEquals(Level::Warning, $handler->getLevel());
    }

    public function testStdOutLog()
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

        $instance->addStdOutLog(
            Level::Info,
            'stdout-log'
        );
        $logger = $instance->getContainer()->get('logger');
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $logger = $instance->log();
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $handler = $logger->getHandlers()[0];
        $this->assertEquals(Level::Info, $handler->getLevel());
    }

    public function testStdErrLog()
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

        $instance->addStdErrLog(
            Level::Emergency,
            'stderr-log'
        );
        $logger = $instance->getContainer()->get('logger');
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $logger = $instance->log();
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
        /* @var $logger \Monolog\Logger */
        $handler = $logger->getHandlers()[0];
        $this->assertEquals(Level::Emergency, $handler->getLevel());
    }
    
    protected function tearDown(): void
    {
        if (isset($this->fileLogPath) && file_exists($this->fileLogPath)) {
            @unlink($this->fileLogPath);
        }
    }
}
