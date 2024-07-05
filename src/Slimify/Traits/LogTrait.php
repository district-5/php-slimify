<?php /** @noinspection PhpUnused */

namespace Slimify\Traits;

use DI\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Trait LogTrait
 * @package Slimify\Traits
 */
trait LogTrait
{
    /**
     * @param string $logFilePath
     * @param int|Level $level (optional) default 100 (Debug)
     * @param int $maxFiles (optional) default 5
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addFileLog(string $logFilePath, int|Level $level = Level::Debug, int $maxFiles = 5, string $name = 'app'): static
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($logFilePath, $level, $maxFiles, $name) {
                $logger = new Logger($name);
                $formatter = new LineFormatter();
                $fileHandler = new RotatingFileHandler($logFilePath, $maxFiles, $level);
                $fileHandler->setFilenameFormat('{date}-{filename}', 'Y-m-d');
                $fileHandler->setFormatter($formatter);
                $fileHandler->setLevel($level);
                $logger->pushHandler(
                    $fileHandler
                );
                return $logger;
            }
        );
        return $this;
    }

    /**
     * @param int|Level $level (optional) default 100 (Debug)
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addStdOutLog(int|Level $level = Level::Debug, string $name = 'app'): static
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($level, $name) {
                $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

                $streamHandler = new StreamHandler('php://stdout', $level);
                $streamHandler->setFormatter($formatter);
                $streamHandler->setLevel($level);

                $logger = new Logger($name);
                $logger->pushHandler($streamHandler);
                return $logger;
            }
        );
        return $this;
    }

    /**
     * @param int|Level $level (optional) default 100 (Debug)
     * @param string $name (optional) default 'app'
     * @return $this
     * @noinspection PhpUnused
     */
    public function addStdErrLog(int|Level $level = Level::Debug, string $name = 'app'): static
    {
        $this->container->set(
            'logger',
            function (/** @noinspection PhpUnusedParameterInspection */ Container $c) use ($level, $name) {
                $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

                $errorHandler = new ErrorLogHandler();
                $errorHandler->setFormatter($formatter);
                $errorHandler->setLevel($level);

                $logger = new Logger($name);
                $logger->pushHandler($errorHandler);
                return $logger;
            }
        );
        return $this;
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
}
