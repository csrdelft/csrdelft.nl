<?php

# -------------------------------------------------------------------
# configuratie.include.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
#
# alle paden goedzetten.
require_once 'defines.include.php';

# uncomment de volgende regel om de boel in onderhoudsmode te ketzen 
#define('MODE', 'ONDERHOUD'); 
# 
# 
# uncomment de volgende regel om de database automatisch te laten controleren
#define('DB_CHECK_ENABLE', 'zie PersistentEntity::checkTable()');
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
if (!defined('MODE')) {
	if (php_sapi_name() === 'cli') {
		define('MODE', 'CLI');
	} else {
		define('MODE', 'WEB');
	}
}

# alle meldingen tonen
error_reporting(E_ALL);

# datum weergave enzo
setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');
date_default_timezone_set('Europe/Amsterdam');

require_once 'common.functions.php';
require_once 'mysql.class.php'; # DEPRECATED

require_once 'MVC/model/PersistenceModel.abstract.php';
require_once 'lid/loginlid.class.php';
require_once 'MVC/model/LidInstellingenModel.class.php';

switch (constant('MODE')) {
	case 'CLI':
		if (!LoginLid::instance()->mag('P_ADMIN')) {
			die('access denied');
		}
		break;

	case 'WEB':

		# geen sessie-id in de url
		ini_set('session.use_only_cookies', 1);
		session_save_path(SESSION_PATH);

		ini_set('upload_tmp_dir', TMP_PATH);

		$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		Instellingen::setTemp('stek', 'request', $req);

		# als er een wikiconfiguratie is en hierin is de csr-wikiauthicatie geselecteerd 
		# dan is de sessie al gestart (en zijn sommige includes niet nodig)
		global $conf;
		if (isset($conf['authtype']) AND $conf['authtype'] === 'authcsr') {
			error_reporting(E_ALL & ~E_NOTICE);
		} else {
			require_once 'MVC/model/Paging.interface.php';

			require_once 'MVC/view/TemplateView.abstract.php';
			require_once 'MVC/view/Formulier.class.php';
			require_once 'MVC/view/CsrUbb.class.php';
			require_once 'MVC/view/CsrLayoutPage.class.php';
			require_once 'MVC/view/CsrLayout2Page.class.php';
			require_once 'MVC/view/CsrLayout3Page.class.php';
			require_once 'simplehtml.class.php'; # DEPRECATED
			require_once 'icon.class.php';

			require_once 'MVC/controller/AclController.abstract.php';

			# sessie starten
			# volgt de defaults van webserver Syrinx, voor consistentie bij testen
			session_name('PHPSESSID');
			session_set_cookie_params(1036800, '/', '', false, false);
			session_start();
		}

		# N.B. het is van belang dat na het starten van de sessie meteen LoginLid
		# wordt geinitialiseerd, omdat die de ingelogde gebruiker controleert en
		# tevens sess_deleted bugs ondervangt en ip-checks doet
		LoginLid::instance();

		if (defined('DB_MODIFY_ENABLE') OR defined('DB_DROP_ENABLE')) {
			if (!LoginLid::mag('P_ADMIN')) {
				header('location: ' . CSR_ROOT . '/onderhoud.html');
				exit;
			}
		}
		break;

	case 'ONDERHOUD':
		header('location: ' . CSR_ROOT . '/onderhoud.html');
		exit;

	default:
		die('configuratie.include.php: "' . MODE . '" unsupported MODE');
}
