<?php

use CsrDelft\controller\api\ApiActiviteitenController;
use CsrDelft\controller\api\ApiAgendaController;
use CsrDelft\controller\api\ApiAuthController;
use CsrDelft\controller\api\ApiForumController;
use CsrDelft\controller\api\ApiLedenController;
use CsrDelft\controller\api\ApiMaaltijdenController;
use \Jacwright\RestServer\RestServer;

require_once 'configuratie.include.php';

$mode = DEBUG ? 'debug' : 'production';
$server = new RestServer($mode);

$server->cacheDir = DATA_PATH . 'restserver/';

$server->addClass(ApiActiviteitenController::class, '/activiteiten');
$server->addClass(ApiAgendaController::class, '/agenda');
$server->addClass(ApiAuthController::class, '/auth');
$server->addClass(ApiForumController::class, '/forum');
$server->addClass(ApiLedenController::class, '/leden');
$server->addClass(ApiMaaltijdenController::class, '/maaltijden');

$server->handle();
