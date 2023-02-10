<?php /** @noinspection PhpMissingParamTypeInspection */

namespace Slimify;

use Psr\Http\Message\ResponseInterface;
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
     * @var SlimifyCors|null
     */
    protected ?SlimifyCors $cors = null;

    /**
     * SlimifyResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
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
        $response = $this->response->withHeader(
            'Content-Type',
            'application/json'
        )->withStatus(
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
        $response = $this->response->withHeader(
            'Content-Type',
            'text/plain'
        )->withHeader(
            'Content-Length',
            strlen($content)
        )->withStatus(
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
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function sendFile(string $content, string $fileName, string $contentType = 'text/plain'): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(
            $content
        );

        return $response->withHeader(
            'Content-Type',
            sprintf('%s; charset=utf-8', $contentType)
        )->withHeader(
            'Content-Disposition', 'attachment; filename=' . $fileName
        )->withStatus(
            200
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
        return $this->response->withHeader(
            'Location',
            $url
        )->withStatus(
            $status
        );
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function addCorsToResponseIfNecessary(ResponseInterface $response): ResponseInterface
    {
        if (null === $this->getCors()) {
            return $response;
        }

        $corsArray = $this->getCors()->getResponseHeaders();
        foreach ($corsArray as $key => $value) {
            $response = $response->withHeader(
                $key,
                $value
            );
        }

        return $response;
    }
}
