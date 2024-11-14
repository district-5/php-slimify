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
use Slimify\SlimifyCors;
use Slimify\SlimifyInstance;
use Slimify\SlimifyResponse;

/**
 * Class SlimifyStaticTest
 * @package SlimifyTests
 */
class SlimifyResponseTest extends TestAbstract
{
    public function testSlimifyResponse()
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

        $instance->setInterfaces(
            new Request(
                'POST',
                new Uri('https', 'example.com', 443, '/test'),
                new Headers(['X-Foo' => ['Bar']]),
                ['IS_A_COOKIE' => 'NOT REALLY'],
                ['IS_A_SERVER' => 'NOT REALLY'],
                new Stream($fakeRequestBody),
            ),
            new Response()
        );

        $response = $instance->response();
        $this->assertInstanceOf(SlimifyResponse::class, $response);
        $this->assertInstanceOf(SlimifyCors::class, $response->getCors());
        $response->unsetCors();
        $this->assertNull($response->getCors());

        $cors = new SlimifyCors();
        $cors->setAllowedHeaders(['X-Foo']);
        $response->setCors($cors);

        $jsonFromArray = $response->json(['foo' => 'bar']);
        $this->assertJson($jsonFromArray->getBody()->__toString());
        $this->assertEquals('{"foo":"bar"}', $jsonFromArray->getBody()->__toString());

        $headers = $jsonFromArray->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Access-Control-Allow-Headers', $headers);
        $this->assertEquals('X-Foo', $headers['Access-Control-Allow-Headers'][0]);
        $response->unsetCors();

        $response->clearResponse();
        $jsonFromString = $response->jsonFromString('{"abc":"123"}');
        $this->assertJson($jsonFromString->getBody()->__toString());
        $this->assertEquals('{"abc":"123"}', $jsonFromString->getBody()->__toString());

        $response->clearResponse();
        $codeResponse = $response->httpCodeResponse(418); // tea pot
        $this->assertEquals(418, $codeResponse->getStatusCode());

        $response->clearResponse();
        $plainText = $response->plainText('Hello World');
        $this->assertEquals('Hello World', $plainText->getBody()->__toString());

        $response->clearResponse();
        $redirect = $response->redirect('https://example.com', 301);
        $this->assertEquals(301, $redirect->getStatusCode());
        $this->assertEquals('https://example.com', $redirect->getHeaderLine('Location'));

        $response->clearResponse();

        SlimifyCors::retrieve()->reset();
        $response->setCors(
            SlimifyCors::retrieve()->setAllowedOrigins(['foo.example.com'])
        );
        $fileResponse = $response->sendFile(
            'foo bar',
            'foobar.txt',
            'no/type',
            'utf-16'
        );
        $this->assertEquals('foo bar', $fileResponse->getBody()->__toString());
        $this->assertEquals('no/type; charset=utf-16', $fileResponse->getHeaderLine('Content-Type'));
        $this->assertEquals('attachment; filename=foobar.txt', $fileResponse->getHeaderLine('Content-Disposition'));
        $this->assertEquals('foo.example.com', $fileResponse->getHeaderLine('Access-Control-Allow-Origin'));

        $tmpFile = sys_get_temp_dir() . '/test-slimify.txt';
        file_put_contents($tmpFile, 'Hello World');
        $streamResponse = $response->serveFileFromStream(
            fopen($tmpFile, 'r'),
            'text/plain',
            'utf-8'
        );
        $this->assertEquals('Hello World', $streamResponse->getBody()->__toString());
        $this->assertEquals('text/plain; charset=utf-8', $streamResponse->getHeaderLine('Content-Type'));
    }

    protected function tearDown(): void
    {
        if (file_exists(sys_get_temp_dir() . '/test-slimify.txt')) {
            unlink(sys_get_temp_dir() . '/test-slimify.txt');
        }
    }
}
