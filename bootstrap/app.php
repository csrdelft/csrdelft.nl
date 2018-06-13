<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

CsrDelft\Orm\Configuration::load(array(
    'cache_path' => DATA_PATH,
    'db' => parse_ini_file(ETC_PATH . 'mysql.ini')
));

// TODO Vind een goede manier om deze constanten weg te halen
// default is website mode
if (php_sapi_name() === 'cli') {
    define('MODE', 'CLI');
} else {
    define('MODE', 'WEB');
}

if (isset($_SERVER['REQUEST_URI'])) {
    $req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
} else {
    $req = null;
}
define('REQUEST_URI', $req);

if (isset($_SERVER['HTTP_REFERER'])) {
    $ref = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
} else {
    $ref = null;
}
define('HTTP_REFERER', $ref);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
