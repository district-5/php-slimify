Slimify
=======

Requirements...
---------------

```json
{
    "php": ">=7.1",
    "slim/slim": "^4.0",
    "slim/php-view": "^2.2",
    "slim/psr7": "^0.5.0",
    "ext-json": "*",
    "php-di/slim-bridge": "^3.0"
}
```

Set up...
---------

```php
<?php
use DI\Container;

/**
 * Build a container
 */
$container = new Container();

/**
 * Instantiate App
 */
$app = \Slimify\SlimifyFactory::createSlimify(
    $container,
    true // Is this the 'development' environment?
);

/**
 * Add default view
 */
$app->addView(
    '/path/to/view/template/folder',
    '/path/to/view/layout.phtml',
    [], // params to inject to all views
    'default' // the view name (you can have multiple views)
);

/**
 * Add a file log.
 */
//$app->addFileLog(
//    '/path/to/log/app.log',
//    \Monolog\Logger::DEBUG,
//    1,
//    'app'
//);

/**
 * Add stdout log.
 */
$app->addStdOutLog(
    \Monolog\Logger::DEBUG,
    1,
    'app'
);

/**
 * Add router
 */
$app->addRouterWithCache(
    '/path/to/cache/routes.php',
    true
);

/**
 * Add any middleware
 */
// $app->add(new MyMiddleware());

include '/path/to/my/routes.php';

/**
 * Add any params to inject into the error views.
 */
\Slimify\SlimifyStatic::retrieve()->setErrorViewParams(
    [
        'css' => '/some/file'
    ]
);

/**
 * Add middleware for error handling
 */
$app->addErrorMiddleware(
    $app->isDevelopment(),
    true,
    true
)->setDefaultErrorHandler(
    \SlimifyMiddleware\ErrorHandlingMiddleware::class
);

/**
 * Run app
 */
$app->run();

```

Error handling...
-----------------

Error handling expects there to be an `error` directory in your view template path. The following files are required:

```
error-access-denied.phtml
error-bad-request.phtml
error-generic.phtml
error-not-found.phtml
```

Usage...
--------

In the beginning of your route, make sure you call `setInterfaces`, passing the request and response objects.

```php
<?php
/* @var $app \Slimify\SlimifyInstance */

use Slim\Psr7\Request;
use Slim\Psr7\Response;

$app->get('/', function (Request $request, Response $response, $args) use ($app) {
    $app->setInterfaces($request, $response);

    return $app->getView()->render(
        $response,
        'my-view.phtml',
        [
        ]
    );
});

```
