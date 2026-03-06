<?php /** @noinspection PhpMissingParamTypeInspection */

namespace Slimify;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Header;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

/**
 * Class SlimifyResponse
 * @package Slimify
 */
class SlimifyResponse
{
    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * @var RequestInterface|null
     */
    protected ?RequestInterface $request = null;

    /**
     * @var SlimifyCors|null
     */
    protected ?SlimifyCors $cors = null;

    /**
     * @var Headers|null
     */
    protected Headers|null $headers = null;

    /**
     * SlimifyResponse constructor.
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
        $cors = SlimifyCors::retrieve();
        if ($cors->hasBeenConfigured()) {
            $this->setCors($cors);
        }
    }

    /**
     * @param SlimifyCors $cors
     * @return $this
     */
    public function setCors(SlimifyCors $cors): self
    {
        $this->cors = $cors;
        return $this;
    }

    /**
     * Remove cors from the response.
     *
     * @return $this
     */
    public function unsetCors(): self
    {
        $this->cors = null;
        return $this;
    }

    /**
     * @return SlimifyCors|null
     */
    public function getCors(): ?SlimifyCors
    {
        return $this->cors;
    }

    /**
     * @param array|object $payload
     * @param int $status
     * @return ResponseInterface
     */
    public function json($payload, $status = 200): ResponseInterface
    {
        return $this->jsonFromString(
            json_encode($payload),
            $status
        );
    }

    /**
     * @param string $payload
     * @param int $status
     * @return ResponseInterface
     */
    public function jsonFromString(string $payload, $status = 200): ResponseInterface
    {
        $this->addHeader('Content-Type', 'application/json');
        $this->buildResponse();

        $response = $this->response->withStatus(
            $status
        );

        $response->getBody()->write(
            $payload
        );

        return $this->addCorsToResponseIfNecessary(
            $response
        );
    }

    /**
     * @param int $code
     * @return Response
     */
    public function httpCodeResponse(int $code): Response
    {
        return $this->addCorsToResponseIfNecessary(
            $this->response->withStatus($code)
        );
    }

    /**
     * @param string $content
     * @param int $status
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function plainText($content, $status = 200): ResponseInterface
    {
        $this->addHeader('Content-Type', 'text/plain');
        $this->addHeader('Content-Length', strlen($content));
        $response = $this->response->withStatus(
            $status
        );
        $response->getBody()->write(
            $content
        );
        return $response;
    }

    /**
     * @param string $content
     * @param string $fileName
     * @param string $contentType
     * @param string $encoding
     * @return ResponseInterface
     */
    public function sendFile(string $content, string $fileName, string $contentType = 'text/plain', string $encoding = 'utf-8'): ResponseInterface
    {
        $this->response = new Response();
        $this->response->getBody()->write(
            $content
        );
        $this->addHeader('Content-Type', sprintf('%s; charset=%s', $contentType, $encoding));
        $this->addHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        $this->buildResponse();

        return $this->addCorsToResponseIfNecessary(
            $this->response->withStatus(
                200
            )
        );
    }

    /**
     * @param resource $stream
     * @param string $contentType
     * @param string $encoding
     * @return ResponseInterface
     */
    public function serveFileFromStream($stream, string $contentType = 'text/plain', string $encoding = 'utf-8'): ResponseInterface
    {
        $this->response = new Response();
        $this->response->getBody()->write(
            stream_get_contents($stream)
        );

        $this->addHeader('Content-Type', sprintf('%s; charset=%s', $contentType, $encoding));
        $this->buildResponse();

        return $this->addCorsToResponseIfNecessary(
            $this->response->withStatus(
                200
            )
        );
    }

    /**
     * @param string $url
     * @param int $status
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function redirect($url, $status = 302): ResponseInterface
    {
        $this->addHeader('Location', $url);
        $this->buildResponse();

        return $this->response->withStatus(
            $status
        );
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function addCorsToResponseIfNecessary(ResponseInterface $response): ResponseInterface
    {
        if (null === $this->getCors()) {
            return $response;
        }

        $requestOrigin = null;
        if ($this->request !== null) {
            $requestOrigin = $this->request->getHeaderLine('Origin');
        }

        $corsArray = $this->getCors()->getResponseHeaders($requestOrigin);
        foreach ($corsArray as $key => $value) {
            $response = $response->withHeader(
                $key,
                $value
            );
        }

        return $response;
    }

    /**
     * Clear the body of the response.
     */
    public function clearResponse(): void
    {
        $responseClass = get_class($this->response);
        $this->headers = new Headers();
        $this->response = new $responseClass();
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addHeader(string $name, string $value): void
    {
        if (!isset($this->headers)) {
            $this->headers = new Headers();
        }
        $this->headers->removeHeader($name);
        $this->headers->addHeader(
            $name,
            $value
        );
    }

    /**
     * @param bool $originalCase
     * @return array
     */
    public function getHeaders(bool $originalCase = false): array
    {
        if (!isset($this->headers)) {
            $this->headers = new Headers();
        }

        return $this->headers->getHeaders($originalCase);
    }

    /**
     * @return void
     */
    protected function buildResponse(): void
    {
        $headers = $this->getHeaders(true);
        foreach ($headers as $key => $value) {
            $this->response = $this->response->withHeader(
                $key,
                $value[0]
            );
        }
    }
}
