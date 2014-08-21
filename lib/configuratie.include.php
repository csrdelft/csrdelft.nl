<?php

# -------------------------------------------------------------------
# configuratie.include.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
# 
# uncomment de volgende twee regels om de boel in onderhoudsmode te ketzen 
#header('location: http://csrdelft.nl/onderhoud.html');
#exit;

register_shutdown_function('fatal_handler');

function fatal_handler() {
	$error = error_get_last();
	if ($error !== null) {
		$debug['error'] = $error;
		$debug['trace'] = debug_backtrace(false);
		$debug['POST'] = $_POST;
		$debug['GET'] = $_GET;
		$debug['SERVER'] = $_SERVER;
		if ($error['type'] === E_CORE_ERROR OR $error['type'] === E_ERROR) {
			$headers[] = 'From: pubcie@csrdelft.nl';
			$headers[] = 'Content-Type: text/plain; charset=UTF-8';
			$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
			mail('pubcie@csrdelft.nl', 'Fatal error failsafe', print_r($debug, true), implode("\r\n", $headers));
		} else {
			DebugLogModel::instance()->log(__FILE__, 'fatal_handler', func_get_args(), print_r($debug, true));
		}
	}
}

# alle meldingen tonen
error_reporting(E_ALL);

# datum weergave enzo
setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');
date_default_timezone_set('Europe/Amsterdam');

# default is website mode
if (php_sapi_name() === 'cli') {
	define('MODE', 'CLI');
} else {
	define('MODE', 'WEB');
}

# Defines
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

# Model
require_once 'defines.include.php';
require_once 'common.functions.php';
require_once 'MijnSqli.class.php'; # DEPRECATED
require_once 'MVC/model/PersistenceModel.abstract.php';
require_once 'MVC/model/DebugLogModel.class.php';
require_once 'MVC/model/LoginModel.class.php';
require_once 'MVC/model/LidInstellingenModel.class.php';
require_once 'MVC/model/Paging.interface.php';

# View
require_once 'MVC/view/JsonResponse.class.php';
require_once 'MVC/view/SmartyTemplateView.abstract.php';
require_once 'MVC/view/Formulier.class.php';
require_once 'MVC/view/CsrUbb.class.php';
require_once 'MVC/view/CsrLayoutPage.class.php';
require_once 'MVC/view/CsrLayout2Page.class.php';
require_once 'MVC/view/CsrLayout3Page.class.php';
require_once 'simplehtml.class.php'; # DEPRECATED
require_once 'icon.class.php';

# Controller
require_once 'MVC/controller/AclController.abstract.php';

# Router
switch (constant('MODE')) {
	case 'CLI':
		if (!LoginModel::mag('P_ADMIN')) {
			die('access denied');
		}
		break;

	case 'WEB':
		if (defined('DB_MODIFY_ENABLE') OR defined('DB_DROP_ENABLE')) {
			if (!LoginModel::mag('P_ADMIN')) {
				redirect(CSR_ROOT . '/onderhoud.html');
				exit;
			}
		}
		# terugvinden van temp upload files
		ini_set('upload_tmp_dir', TMP_PATH);

		# geen sessie-id in de url
		ini_set('session.use_only_cookies', 1);
		session_save_path(SESSION_PATH);

		# sessie starten
		session_name('PHPSESSID');
		session_set_cookie_params(1036800, '/', '', false, false);
		session_start();

		LoginModel::instance();
		break;

	default:
		die('configuratie.include.php unsupported MODE: ' . MODE);
}
