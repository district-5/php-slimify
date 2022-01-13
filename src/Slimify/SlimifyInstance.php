<?php
namespace Slimify;

use DI\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
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
    protected $storage = [];

    /**
     * @var PhpRenderer[]
     */
    protected $views = [];

    /**
     * @var bool
     */
    protected $development;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array|null
     */
    protected $bodyParams = null;

    /**
     * @var array|stdClass
     */
    protected $jsonBody = null;

    /**
     * @var array|null
     */
    protected $queryParams = null;

    /**
     * @var ErrorMiddleware
     */
    protected $errorMiddleware = null;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var SlimifyFlashMessages|null
     */
    protected $flashMessages = null;

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
    public function addView(string $templatePath, string $layout, array $params = [], $key = 'default')
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
     */
    public function getView($key = 'default')
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
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param string $cacheLocation
     * @param bool $deleteInDevelopment
     * @return $this
     */
    public function addRouterWithCache(string $cacheLocation, $deleteInDevelopment = true): SlimifyInstance
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
     * @noinspection PhpUndefinedClassInspection
     */
    public function addFileLog(string $logFilePath, int $level = Logger::DEBUG, int $maxFiles = 5, string $name = 'app'): SlimifyInstance
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */Container $c) use ($logFilePath, $level, $maxFiles, $name) {
                $logger = new Logger($name);
                $formatter = new \Monolog\Formatter\LineFormatter();
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
     */
    public function addStdOutLog(int $level = Logger::DEBUG, string $name = 'app'): SlimifyInstance
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */Container $c) use ($level, $name) {
                $output = "[%datetime%] %channel%.%level_name%: %message%\n";
                $formatter = new LineFormatter($output);

                $streamHandler = new StreamHandler('php://stdout', $level);
                $streamHandler->setFormatter($formatter);

                $logger = new Logger('LoggerName');
                $logger->pushHandler($streamHandler);
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
    public function response()
    {
        return new SlimifyResponse(
            $this->response
        );
    }

    public function flash()
    {
        if ($this->flashMessages === null) {
            $this->flashMessages = new SlimifyFlashMessages();
        }
        return $this->flashMessages;
    }

    /**
     * @return array
     */
    public function getAllPostParams()
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
     */
    public function getJsonBody($asArray = true)
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
        if ($string === null || strlen($string) < 3) {
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
     */
    public function getIp()
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
     * @param null|mixed $default
     * @return int|null|mixed
     */
    public function getPostParamInt(string $key, $default = null)
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
     * @param null|mixed $default
     * @return int|null|mixed
     */
    public function getQueryParamInt(string $key, $default = null)
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
     * @return int|mixed|null
     */
    public function getPageNumber(string $key = 'page', int $default = 1)
    {
        return $this->getQueryParamInt($key, $default);
    }

    /**
     * @param mixed $val
     * @param mixed $default
     * @return int
     */
    protected function paramToInt($val, $default)
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
     */
    public function log()
    {
        return $this->container->get('logger');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return $this
     */
    public function setInterfaces(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function setArguments(array $args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->args;
    }

    /**
     * @param string $key
     * @param mixed $default (optional) default null
     * @return mixed|null
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
     */
    public function isAjaxRequest()
    {
        return ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest');
    }
}
