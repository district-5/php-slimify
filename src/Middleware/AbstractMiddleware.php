<?php

namespace Slimify\Middleware;

use Slim\App;

/**
 * Class AbstractMiddleware
 * @noinspection PhpUnused
 * @package Slimify\Middleware
 */
abstract class AbstractMiddleware
{
    /**
     * Add the middleware to the app.
     *
     * @param App $app
     */
    abstract public static function add(App $app): void;
}
