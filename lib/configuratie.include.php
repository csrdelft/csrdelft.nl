<?php

# -------------------------------------------------------------------
# configuratie.include.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
#
# alle paden goedzetten.
require_once 'defines.include.php';

# uncomment de volgende twee regels om de boel in onderhoudsmode te ketzen 
#header('location: ' . CSR_ROOT . '/onderhoud.html');
#exit;
# 
# 
# uncomment de volgende regel om de database automatisch te laten controleren
define('DB_CHECK_ENABLE', 'zie PersistentEntity::checkTable()');
# 
# uncomment de volgende regel om de database automatisch te laten bijwerken
#define('DB_MODIFY_ENABLE', 'heb je een backup gemaakt?');
#
# uncomment de volgende regel om de database automatisch te laten droppen
#define('DB_DROP_ENABLE', 'heb je een backup gemaakt?');
#
#
# wordt gebruikt om pagina's alleen op Confide te laten zien
define('CONFIDE_IP', '80.112.180.123');

# default is website mode
if (php_sapi_name() === 'cli') {
	define('MODE', 'CLI');
} else {
	define('MODE', 'WEB');
}

# alle meldingen tonen
error_reporting(E_ALL);

# datum weergave enzo
setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');
date_default_timezone_set('Europe/Amsterdam');

# Model
require_once 'common.functions.php';
require_once 'MijnSqli.class.php'; # DEPRECATED
require_once 'MVC/model/PersistenceModel.abstract.php';
require_once 'MVC/model/LoginModel.class.php';
require_once 'MVC/model/LidInstellingenModel.class.php';
require_once 'MVC/model/Paging.interface.php';
# View
require_once 'MVC/view/TemplateView.abstract.php';
require_once 'MVC/view/Formulier.class.php';
require_once 'MVC/view/CsrUbb.class.php';
require_once 'MVC/view/CsrLayoutPage.class.php';
require_once 'MVC/view/CsrLayout2Page.class.php';
require_once 'MVC/view/CsrLayout3Page.class.php';
require_once 'simplehtml.class.php'; # DEPRECATED
require_once 'icon.class.php';
# Controller
require_once 'MVC/controller/AclController.abstract.php';

switch (constant('MODE')) {
	case 'CLI':
		if (!LoginModel::mag('P_ADMIN')) {
			die('access denied');
		}
		break;

	case 'WEB':
		ini_set('upload_tmp_dir', TMP_PATH);

		# geen sessie-id in de url
		ini_set('session.use_only_cookies', 1);
		session_save_path(SESSION_PATH);

		# als er een wikiconfiguratie is en hierin is de csr-wikiauthicatie geselecteerd 
		# dan is de sessie al gestart (en zijn sommige includes niet nodig)
		global $conf;
		if (isset($conf['authtype']) AND $conf['authtype'] === 'authcsr') {
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
		} else {
			# sessie starten
			# volgt de defaults van webserver Syrinx, voor consistentie bij testen
			session_name('PHPSESSID');
			session_set_cookie_params(1036800, '/', '', false, false);
			session_start();
		}

		$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		Instellingen::setTemp('stek', 'request', $req);

		$ref = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		Instellingen::setTemp('stek', 'referer', $ref);

		# N.B. het is van belang dat na het starten van de sessie meteen LoginModel
		# wordt geinitialiseerd, omdat die de ingelogde gebruiker controleert en
		# tevens sess_deleted bugs ondervangt en ip-checks doet
		LoginModel::instance();

		if (defined('DB_MODIFY_ENABLE') OR defined('DB_DROP_ENABLE')) {
			if (!LoginModel::mag('P_ADMIN')) {
				header('location: ' . CSR_ROOT . '/onderhoud.html');
				exit;
			}
		}
		break;

	default:
		die('configuratie.include.php: "' . MODE . '" unsupported MODE');
}
