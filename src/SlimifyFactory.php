<?php /** @noinspection PhpUnused */

namespace Slimify;

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

/**
 * Class SlimifyFactory
 * @package Slimify
 */
class SlimifyFactory extends AppFactory
{
    /**
     * @param ContainerInterface $container
     * @param bool $isDevelopment
     * @return SlimifyInstance
     */
    public static function createSlimify(ContainerInterface $container, bool $isDevelopment): SlimifyInstance
    {
        $instance = new SlimifyInstance(
            self::determineResponseFactory(),
            $container,
            static::$callableResolver,
            static::$routeCollector,
            static::$routeResolver,
            static::$middlewareDispatcher
        );
        $instance->setContainer($container);
        $instance->setDevelopment($isDevelopment);
        SlimifyStatic::retrieve($instance);

        return $instance;
    }
}
