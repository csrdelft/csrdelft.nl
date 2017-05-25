<?php

use CsrDelft\controller\api\ApiActiviteitenController;
use CsrDelft\controller\api\ApiAgendaController;
use CsrDelft\controller\api\ApiAuthController;
use CsrDelft\controller\api\ApiForumController;
use CsrDelft\controller\api\ApiLedenController;
use CsrDelft\controller\api\ApiMaaltijdenController;
use Jacwright\RestServer\RestServer;

/**
 * Maak de API toegankelijk vanaf bepaalde externe Origins.
 */
$enabledOrigins = array(
	'http://localhost:8100', // Nodig voor de iOS app
	'https://csrdelft.github.io' // Gebruikt voor app staging
);
if (in_array($_SERVER['HTTP_ORIGIN'], $enabledOrigins, true)) {
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	header('Access-Control-Max-Age: 1440');
	header('Access-Control-Allow-Headers: Accept, Origin, Content-Type, X-Csr-Authorization');
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		http_response_code(204);
		exit;
	}
}

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
