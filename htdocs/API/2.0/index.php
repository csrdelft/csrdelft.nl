<?php

use CsrDelft\common\ContainerFacade;
use CsrDelft\controller\api\ApiActiviteitenController;
use CsrDelft\controller\api\ApiAgendaController;
use CsrDelft\controller\api\ApiAuthController;
use CsrDelft\controller\api\ApiForumController;
use CsrDelft\controller\api\ApiLedenController;
use CsrDelft\controller\api\ApiMaaltijdenController;
use CsrDelft\controller\api\ApiSponsorlinksController;
use CsrDelft\Kernel;
use Jacwright\RestServer\RestServer;
use Symfony\Component\HttpFoundation\Request;

require_once 'configuratie.include.php';

/**
 * Maak de API toegankelijk vanaf bepaalde externe domeinen.
 */
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], explode(',', API_ORIGINS), true)) {
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	header('Access-Control-Max-Age: 1440');
	header('Access-Control-Allow-Headers: Accept, Origin, Content-Type, X-Csr-Authorization');
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		http_response_code(204);
		exit;
	}
}

$mode = DEBUG ? 'debug' : 'production';
$server = new RestServer($mode);

$server->root = '/API/2.0';
$server->cacheDir = VAR_PATH . 'restserver/';

is_dir($server->cacheDir) || mkdir($server->cacheDir, 0777, true);

$server->addClass(ApiActiviteitenController::class, '/activiteiten');
$server->addClass(ApiAgendaController::class, '/agenda');
$server->addClass(ApiAuthController::class, '/auth');
$server->addClass(ApiForumController::class, '/forum');
$server->addClass(ApiLedenController::class, '/leden');
$server->addClass(ApiMaaltijdenController::class, '/maaltijden');
$server->addClass(ApiSponsorlinksController::class, '/sponsorlinks');

$server->handle();
