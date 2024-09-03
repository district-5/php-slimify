<?php

namespace Slimify\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;

/**
 * Class GzipMiddleware
 * @noinspection PhpUnused
 * @package Slimify\Middleware
 */
class GzipMiddleware extends AbstractMiddleware
{
    /**
     * Add Gzip middleware to the app.
     *
     * @param App $app
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public static function add(App $app): void
    {
        $app->add(function (Request $request, RequestHandlerInterface $handler) {
            if ($request->hasHeader('Accept-Encoding')) {
                if (stristr($request->getHeaderLine('Accept-Encoding'), 'gzip') === false) {
                    return $handler->handle( // No support for gzip.
                        $request
                    );
                }
            }

            $response = $handler->handle(
                $request
            );

            if ($response->hasHeader('Content-Encoding')) {
                return $handler->handle(
                    $request
                );
            }

            // Compress response data
            $deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
            $compressed = deflate_add($deflateContext, (string)$response->getBody(), ZLIB_FINISH);

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $compressed);
            rewind($stream);

            return $response->withHeader(
                'Content-Encoding',
                'gzip'
            )->withHeader(
                'Content-Length',
                strlen($compressed)
            )->withBody(
                new Stream($stream)
            );
        });
    }
}
