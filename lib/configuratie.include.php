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

function fatal_handler(Exception $ex = null) {

	if (TIME_MEASURE) {
		TimerModel::instance()->log();
	}

	if ($ex instanceof Exception) {
		try {
			if (LoginModel::mag('P_LOGGED_IN')) {
				echo str_replace('#', '<br />#', $ex); // stacktrace
			}
			printDebug();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	$error = error_get_last();
	if ($error !== null) {
		$debug['error'] = $error;
		$debug['trace'] = debug_backtrace(false);
		$debug['POST'] = $_POST;
		$debug['GET'] = $_GET;
		$debug['SERVER'] = $_SERVER;
		if ($error['type'] === E_CORE_ERROR OR $error['type'] === E_ERROR) {

			if (DEBUG) {
				DebugLogModel::instance()->log(__FILE__, 'fatal_handler', func_get_args(), print_r($debug, true));
			} else {
				$headers[] = 'From: Fatal error handler <pubcie@csrdelft.nl>';
				$headers[] = 'Content-Type: text/plain; charset=UTF-8';
				$headers[] = 'X-Mailer: nl.csrdelft.lib.Mail';
				$subject = 'Fatal error on request ';
				if (isset($_SERVER['SCRIPT_URL'])) {
					$subject .= filter_var($_SERVER['SCRIPT_URL'], FILTER_SANITIZE_URL);
				}
				mail('pubcie@csrdelft.nl', $subject, print_r($debug, true), implode("\r\n", $headers));
			}
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
require_once 'model/framework/DynamicEntityModel.class.php';
require_once 'model/framework/CachedPersistenceModel.abstract.php';
require_once 'model/DebugLogModel.class.php';
require_once 'model/TimerModel.class.php';
require_once 'model/AccessModel.class.php';
require_once 'model/LidInstellingenModel.class.php';
require_once 'model/ForumModel.class.php';

# View
require_once 'view/JsonResponse.class.php';
require_once 'view/SmartyTemplateView.abstract.php';
require_once 'view/formulier/DataTable.class.php';
require_once 'view/CsrBB.class.php';
require_once 'view/CsrLayoutPage.class.php';
require_once 'view/CsrLayout2Page.class.php';
require_once 'icon.class.php';

# Controller
require_once 'controller/AclController.abstract.php';

# Router
switch (constant('MODE')) {
	case 'CLI':
		if (!LoginModel::mag('P_ADMIN')) {
			die('access denied');
		}
		break;

	case 'WEB':
		# terugvinden van temp upload files
		ini_set('upload_tmp_dir', TMP_PATH);

		# geen sessie-id in de url
		ini_set('session.use_only_cookies', 1);
		session_save_path(SESSION_PATH);

		# sessie starten
		session_name('PHPSESSID');
		session_set_cookie_params(1036800, '/', '', false, false);
		session_start();
		/**
		 * Werkomheen
		 * @source www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
		 */
		if (session_id() == 'deleted') {
			session_regenerate_id();
		}
		# Prefetch
		Instellingen::instance()->prefetch();
		LidInstellingen::instance()->prefetch('uid = ?', array(LoginModel::getUid()));
		VerticalenModel::instance()->prefetch();
		ForumModel::instance()->prefetch();

		# database modus meldingen
		if (DB_MODIFY OR DB_DROP) {
			if (DEBUG) {
				if (DB_DROP) {
					setMelding('DB_DROP enabled', 2);
				}
			} elseif (!LoginModel::mag('P_ADMIN')) {
				redirect(CSR_ROOT . '/onderhoud.html');
			} elseif (DB_DROP) {
				setMelding('DB_DROP enabled', 2);
			} elseif (DB_MODIFY) {
				setMelding('DB_MODIFY enabled', 2);
			}
		}
		break;

	default:
		die('configuratie.include.php unsupported MODE: ' . MODE);
}