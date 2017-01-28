<?php

use \Jacwright\RestServer\RestServer;

require_once 'configuratie.include.php';

require 'controller/api/ActiviteitenController.class.php';
require 'controller/api/AgendaController.class.php';
require 'controller/api/AuthController.class.php';
require 'controller/api/ForumController.class.php';
require 'controller/api/LedenController.class.php';
require 'controller/api/MaaltijdenController.class.php';

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
