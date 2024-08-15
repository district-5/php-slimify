<?php /** @noinspection PhpUnused */

namespace Slimify;

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slimify\Traits\ArgumentsTrait;
use Slimify\Traits\EnvironmentTrait;
use Slimify\Traits\FlashMessagesTrait;
use Slimify\Traits\HttpNativeParamsTrait;
use Slimify\Traits\JsonParamsTrait;
use Slimify\Traits\LogTrait;
use Slimify\Traits\RequestHeaderValuesTrait;
use Slimify\Traits\ViewTrait;

/**
 * Class SlimifyInstance
 * @package Slimify
 */
class SlimifyInstance extends App
{
    use ArgumentsTrait;
    use EnvironmentTrait;
    use FlashMessagesTrait;
    use HttpNativeParamsTrait;
    use JsonParamsTrait;
    use LogTrait;
    use RequestHeaderValuesTrait;
    use ViewTrait;

    /**
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * @var Response|null
     */
    protected ?Response $response = null;

    /**
     * @var ErrorMiddleware|null
     */
    protected ?ErrorMiddleware $errorMiddleware = null;

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container): SlimifyInstance
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return parent::getContainer();
    }

    /**
     * @param string $cacheLocation
     * @param bool $deleteInDevelopment
     * @return $this
     * @noinspection PhpUnused
     */
    public function addRouterWithCache(string $cacheLocation, bool $deleteInDevelopment = true): SlimifyInstance
    {
        if ($deleteInDevelopment === true && $this->isDevelopment() === true && file_exists($cacheLocation) && is_writable($cacheLocation)) {
            unlink($cacheLocation);
        }
        $this->addRoutingMiddleware();
        $this->getRouteCollector()->setCacheFile(
            $cacheLocation
        );

        return $this;
    }

    /**
     * Get an instance of the response helper.
     *
     * @return SlimifyResponse
     */
    public function response(): SlimifyResponse
    {
        return new SlimifyResponse(
            $this->request,
            $this->response
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return $this
     * @noinspection PhpUnused
     */
    public function setInterfaces(Request $request, Response $response): SlimifyInstance
    {
        $this->request = $request;
        $this->response = $response;
        return $this;
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
    }
}
