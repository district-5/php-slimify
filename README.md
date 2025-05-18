Slimify
=======

[![CI](https://github.com/district-5/php-slimify/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/district-5/php-slimify/actions)
[![Latest Stable Version](http://poser.pugx.org/district5/slimify/v)](https://packagist.org/packages/district5/slimify)
[![PHP Version Require](http://poser.pugx.org/district5/slimify/require/php)](https://packagist.org/packages/district5/slimify)
[![Codecov](https://codecov.io/gh/district-5/php-slimify/branch/master/graph/badge.svg)](https://codecov.io/gh/district-5/php-slimify)

Installation...
---------------

```
composer require district5/slimify
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
//    '/path/to/log/app.log', // log file
//    \Monolog\Logger::DEBUG, // log level
//    5, // number of files to keep
//    'app' // logger name
//);

/**
 * Add stdout log.
 */
$app->addStdOutLog(
    \Monolog\Logger::DEBUG,
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

$app->post('/json-endpoint', function (Request $request, Response $response, $args) use ($app) {
    $app->setInterfaces($request, $response);

    $jsonParser = $app->getJsonParser();
    $mixedValue = $jsonParser->anything('someKey');
    $arrayValue = $jsonParser->array('arrayKey');
    $boolValue = $jsonParser->bool('boolKey');
    $floatValue = $jsonParser->float('floatKey');
    $intValue = $jsonParser->int('intKey');
    $stringValue = $jsonParser->string('stringKey');
    $mongoIdValue = $jsonParser->mongoId('mongoIdKey');

    // Do something with the values...

    return $app->response()->json([
        'success' => true
    ]);
])
});

```
