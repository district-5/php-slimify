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
    protected $response = null;

    /**
     * SlimifyResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param array|object $payload
     * @param int $status
     * @return ResponseInterface
     */
    public function json($payload, $status = 200)
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
    public function jsonFromString(string $payload, $status = 200)
    {
        $response = $this->response->withHeader(
            'Content-Type',
            'application/json'
        );
        $response->getBody()->write(
            $payload
        );
        return $response;
    }

    /**
     * @param string $content
     * @param int $status
     * @return ResponseInterface
     */
    public function plainText($content, $status = 200)
    {
        $response = $this->response->withHeader(
            'Content-Type',
            'text/plain'
        )->withHeader('Content-Length', strlen($content));
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
     */
    public function sendFile(string $content, string $fileName, string $contentType = 'text/plain')
    {
        $response = new Response();
        $response->getBody()->write(
            $content
        );
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
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
     */
    public function redirect($url, $status = 302)
    {
        return $this->response->withHeader(
            'Location',
            $url
        )->withStatus(
            $status
        );
    }
}
