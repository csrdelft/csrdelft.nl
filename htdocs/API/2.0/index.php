<?php

use \Jacwright\RestServer\RestServer;

require_once 'configuratie.include.php';

require 'api/controller/ActiviteitenController.class.php';
require 'api/controller/AgendaController.class.php';
require 'api/controller/AuthController.class.php';
require 'api/controller/ForumController.class.php';
require 'api/controller/LedenController.class.php';
require 'api/controller/MaaltijdenController.class.php';

$mode = DEBUG ? 'debug' : 'production';
$server = new RestServer($mode);

$server->cacheDir = DATA_PATH . 'restserver/';

$server->addClass('ApiActiviteitenController', '/activiteiten');
$server->addClass('ApiAgendaController', '/agenda');
$server->addClass('ApiAuthController', '/auth');
$server->addClass('ApiForumController', '/forum');
$server->addClass('ApiLedenController', '/leden');
$server->addClass('ApiMaaltijdenController', '/maaltijden');

$server->handle();
