<?php

# -------------------------------------------------------------------
# configuratie.include.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
# 
# uncomment de volgende regel om de boel in onderhoudsmode te ketzen
#define('MODE', 'ONDERHOUD');
# 
# uncomment de volgende regel om de database automatisch te laten controleren
define('DB_CHECK_ENABLE', null);
# 
# uncomment de volgende regel om de database automatisch te laten bijwerken
define('DB_MODIFY_ENABLE', null);
#
# uncomment de volgende regel om de database automatisch te laten droppen
#define('DB_DROP_ENABLE', null);
# 
# de wiki genereert de nodige notices. En heeft daarom de error_reporting 
# anders ingesteld.
global $conf;
if (!(isset($conf['authtype']) AND $conf['authtype'] == 'authcsr')) {
	define('DEBUG', 'DEBUG');
}

if (defined('DEBUG')) {
	error_reporting(E_ALL);
}

# default to website mode
# [ WEB, CLI, BOT ]
if (!defined('MODE')) {
	define('MODE', 'WEB');
}

//alle paden goedzetten.
require_once('include.defines.php');

if (constant('MODE') == 'WEB') {
	# We willen geen sessie-id in de url hebben
	ini_set('session.use_only_cookies', 1);
	session_save_path(SESSION_PATH);

	# wat instellingen
	ini_set('upload_tmp_dir', TMP_PATH);
}

setlocale(LC_ALL, 'nl_NL.utf8');
setlocale(LC_ALL, 'nld_nld');

//standaard templaat voor de C.S.R.-courant
define('COURANT_TEMPLATE', 'courant.tpl');

# wordt gebruikt om pagina's alleen op Confide te laten zien
define('CONFIDE_IP', '80.112.180.123');

//Feut ip voor de rss feed in #csrdelft
define('FEUT_IP', '82.94.188.77');

//id's van de regeltjes in de databasetabel savedquery voor de queues van mededelingen
define('ROWID_QUEUE_MEDEDELINGEN', 62);

//stapeltje dingen includeren die toch (bijna) altijd nodig zijn:
require_once 'common.functions.php';
require_once 'mysql.class.php';
require_once 'MVC/model/Agendeerbaar.interface.php';
require_once 'lid/loginlid.class.php';

switch (constant('MODE')) {
	case 'ONDERHOUD':
		if (!LoginLid::mag('P_ADMIN')) {
			header('location: ' . CSR_ROOT . '/onderhoud.html');
			exit;
		}
	case 'WEB':
		require_once 'MVC/view/Validator.interface.php';
		require_once 'MVC/view/TemplateView.abstract.php';
		require_once 'MVC/view/Formulier.class.php';

		// als er een wikiconfiguratie is en hierin is de csr-wikiauthicatie geselecteerd 
		// dan is de sessie al gestart en zijn sommige includes niet nodig.
		if (!(isset($conf['authtype']) AND $conf['authtype'] == 'authcsr')) {

			require_once 'MVC/model/Paging.interface.php';
			require_once 'MVC/model/PersistenceModel.abstract.php';
			require_once 'MVC/model/LidInstellingenModel.class.php';

			require_once 'MVC/view/CsrLayoutPage.class.php';
			require_once 'MVC/view/CsrLayout2Page.class.php';
			require_once 'MVC/view/CsrUbb.class.php';
			require_once 'simplehtml.class.php';
			require_once 'icon.class.php';

			require_once 'MVC/controller/AclController.abstract.php';
			require_once('MVC/controller/AgendaController.class.php');

			$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
			Instellingen::setTemp('stek', 'request', $req);

			// sessie starten
			// volgt de defaults van webserver Syrinx, zodat testen met een workcopy overeenkomt.
			session_name("PHPSESSID");
			session_set_cookie_params(1036800, '/', '', false, false);

			# N.B. het is van belang dat na het starten van de sessie meteen het databaseobject en het
			# Lid-object worden aangemaakt, omdat die de ingelogde gebruiker controleert, en tevens
			# sess_deleted bugs ondervangt en ip-checks doet
			session_start();
		}
		// database & lid initialiseren...
		$db = MySQL::instance();
		break;
	/*
	  case 'BOT':
	  case 'CLI':
	  $db = MySQL::instance();
	  //TODO: voor bot & cli blijft het nog even $lid ipv LoginLid::instance(), nog geen zin om dat allemaal aan te passen.
	  $lid = LoginLid::instance();
	  break;
	 */
	default:
		die("configuratie.include.php:: unsupported MODE");
}
