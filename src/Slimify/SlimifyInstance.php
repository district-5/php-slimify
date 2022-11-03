<?php /** @noinspection PhpUnused */

namespace Slimify;

use DI\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\PhpRenderer;
use stdClass;

/**
 * Class SlimifyInstance
 * @package Slimify
 */
class SlimifyInstance extends App
{
    /**
     * @var array
     */
    protected array $storage = [];

    /**
     * @var PhpRenderer[]
     */
    protected array $views = [];

    /**
     * @var bool|null
     */
    protected ?bool $development = null;

    /**
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * @var Response|null
     */
    protected ?Response $response = null;

    /**
     * @var array|null
     */
    protected ?array $bodyParams = null;

    /**
     * @var array|stdClass
     */
    protected $jsonBody = null;

    /**
     * @var array|null
     */
    protected ?array $queryParams = null;

    /**
     * @var ErrorMiddleware|null
     */
    protected ?ErrorMiddleware $errorMiddleware = null;

    /**
     * @var array
     */
    protected array $args = [];

    /**
     * @var SlimifyFlashMessages|null
     */
    protected ?SlimifyFlashMessages $flashMessages = null;

    /**
     * Set the view to use, optionally providing 'key' to handle multiple instances of views.
     *
     * @param string $templatePath
     * @param string $layout
     * @param array $params
     * @param string $key
     * @return $this
     * @noinspection PhpUnused
     */
    public function addView(string $templatePath, string $layout, array $params = [], string $key = 'default'): SlimifyInstance
    {
        $view = new PhpRenderer($templatePath);
        $view->setLayout($layout);
        $view->addAttribute('app', $this);
        foreach ($params as $k => $v) {
            $view->addAttribute($k, $v);
        }
        $this->views[$key] = $view;
        return $this;
    }

    /**
     * @param string $key
     * @return PhpRenderer|null
     * @noinspection PhpUnused
     */
    public function getView(string $key = 'default'): ?PhpRenderer
    {
        if (array_key_exists($key, $this->views)) {
            return $this->views[$key];
        }

        return null;
    }

    /**
     * Handles flexible storage within the helper.
     *
     * @param string $key
     * @return mixed|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __get(string $key)
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }
        $container = $this->getContainer();
        if ($container->has($key)) {
            return $container->get($key);
        }
        return null;
    }

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
     * @param string $cacheLocation
     * @param bool $deleteInDevelopment
     * @return $this
     * @noinspection PhpUnused
     */
    public function addRouterWithCache(string $cacheLocation, bool $deleteInDevelopment = true): SlimifyInstance
    {
        if ($deleteInDevelopment === true && $this->isDevelopment() === true && file_exists($cacheLocation)) {
            unlink($cacheLocation);
        }
        $this->addRoutingMiddleware();
        $this->getRouteCollector()->setCacheFile(
            $cacheLocation
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->development;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isProduction(): bool
    {
        return $this->isDevelopment() === false;
    }

    /**
     * @param bool $isDevelopment
     * @return $this
     */
    public function setDevelopment(bool $isDevelopment): SlimifyInstance
    {
        $this->development = $isDevelopment;
        return $this;
    }

    /**
     * @param string $logFilePath
     * @param int $level (optional) default 100 (Debug)
     * @param int $maxFiles (optional) default 5
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addFileLog(string $logFilePath, int $level = Logger::DEBUG, int $maxFiles = 5, string $name = 'app'): SlimifyInstance
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($logFilePath, $level, $maxFiles, $name) {
                $logger = new Logger($name);
                $formatter = new LineFormatter();
                $fileHandler = new RotatingFileHandler($logFilePath, $maxFiles, $level);
                $fileHandler->setFilenameFormat('{date}-{filename}', 'Y-m-d');
                $fileHandler->setFormatter($formatter);
                $logger->pushHandler(
                    $fileHandler
                );
                return $logger;
            }
        );
        return $this;
    }

    /**
     * @param int $level (optional) default 100 (Debug)
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addStdOutLog(int $level = Logger::DEBUG, string $name = 'app'): SlimifyInstance
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($level, $name) {
                $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

                $streamHandler = new StreamHandler('php://stdout', $level);
                $streamHandler->setFormatter($formatter);

                $logger = new Logger($name);
                $logger->pushHandler($streamHandler);
                return $logger;
            }
        );
        return $this;
    }

    /**
     * @param int $level (optional) default 100 (Debug)
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addStdErrLog(int $level = Logger::DEBUG, string $name = 'app'): SlimifyInstance
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($level, $name) {
                $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

                $errorHandler = new ErrorLogHandler();
                $errorHandler->setFormatter($formatter);

                $logger = new Logger($name);
                $logger->pushHandler($errorHandler);
                return $logger;
            }
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
            $this->response
        );
    }

    /**
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     */
    public function flash(): SlimifyFlashMessages
    {
        if ($this->flashMessages === null) {
            $this->flashMessages = new SlimifyFlashMessages();
        }
        return $this->flashMessages;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getAllPostParams(): ?array
    {
        if ($this->bodyParams === null) {
            $this->bodyParams = $this->request->getParsedBody();
        }
        return $this->bodyParams;
    }

    /**
     * Get the JSON decoded body from a request.
     * Defaults to an array, but can be formatted as an object.
     *
     * @param bool $asArray
     * @return array|stdClass
     * @noinspection PhpUnused
     */
    public function getJsonBody(bool $asArray = true)
    {
        if ($this->jsonBody !== null) {
            if ($asArray === true) {
                if (is_array($this->jsonBody)) {
                    return $this->jsonBody;
                }
            } else {
                return $this->jsonBody;
            }
        }
        $body = $this->request->getBody();
        if (!is_object($body)) {
            return null;
        }
        $string = $body->__toString();
        if (strlen($string) < 3) {
            return null;
        }
        if ($asArray === true) {
            $decoded = @json_decode($string, true);
            if (!is_array($decoded)) {
                $this->jsonBody = null;
                return null;
            }
        } else {
            $decoded = @json_decode($string, false);
            if (!is_object($decoded)) {
                $this->jsonBody = null;
                return null;
            }
        }
        $this->jsonBody = $decoded;
        return $this->jsonBody;
    }

    /**
     * @return string|null
     * @noinspection PhpUnused
     */
    public function getIp(): ?string
    {
        $params = $this->request->getServerParams();
        if (array_key_exists('REMOTE_ADDR', $params)) {
            return $params['REMOTE_ADDR'];
        }
        return null;
    }

    /**
     * Get a post parameter (POST).
     *
     * @param string $key
     * @param null|mixed $default
     * @return mixed|null
     */
    public function getPostParam(string $key, $default = null)
    {
        if ($this->bodyParams === null) {
            $this->bodyParams = $this->request->getParsedBody();
        }

        if (is_array($this->bodyParams) && array_key_exists($key, $this->bodyParams)) {
            return $this->bodyParams[$key];
        }
        return $default;
    }

    /**
     * Get an integer post parameter (POST).
     *
     * @param string $key
     * @param int|null $default
     * @return int|null
     * @noinspection PhpUnused
     */
    public function getPostParamInt(string $key, int $default = null): ?int
    {
        return $this->paramToInt(
            $this->getPostParam($key, $default),
            $default
        );
    }

    /**
     * Get a query parameter (GET).
     *
     * @param string $key
     * @param null|mixed $default
     * @return mixed|null
     */
    public function getQueryParam(string $key, $default = null)
    {
        if ($this->queryParams === null) {
            $this->queryParams = $this->request->getQueryParams();
        }

        if (is_array($this->queryParams) && array_key_exists($key, $this->queryParams)) {
            return $this->queryParams[$key];
        }
        return $default;
    }

    /**
     * Get an integer query parameter (GET).
     * @param string $key
     * @param int|null $default
     * @return int|null
     */
    public function getQueryParamInt(string $key, int $default = null): ?int
    {
        return $this->paramToInt(
            $this->getQueryParam($key, $default),
            $default
        );
    }

    /**
     * Get a page number from a request.
     *
     * @param string $key
     * @param int $default
     * @return int|null
     * @noinspection PhpUnused
     */
    public function getPageNumber(string $key = 'page', int $default = 1): ?int
    {
        return $this->getQueryParamInt($key, $default);
    }

    /**
     * @param string|int|mixed $val
     * @param int|null $default
     * @return int
     * @noinspection PhpUnused
     */
    protected function paramToInt($val, int $default = null): int
    {
        if (is_int($val)) {
            return $val;
        }
        if (is_numeric($val) && strstr($val, '.') === false) {
            if (strlen($val) > 1 && substr($val, 0, 1) === '0') {
                return $default;
            }
            return intval($val);
        }
        return $default;
    }

    /**
     * @return Logger
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @noinspection PhpUnused
     */
    public function log(): Logger
    {
        return $this->container->get('logger');
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
     * @param array $args
     * @return $this
     * @noinspection PhpUnused
     */
    public function setArguments(array $args): SlimifyInstance
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @param string $key
     * @param mixed $default (optional) default null
     * @return mixed|null
     * @noinspection PhpUnused
     */
    public function getArgument(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->args)) {
            return null;
        }
        if (strlen($this->args[$key]) > 0) {
            return $this->args[$key];
        }
        return $default;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isAjaxRequest(): bool
    {
        return ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest');
    }
}
