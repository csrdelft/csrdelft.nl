<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# configuratie.include.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
#

//uncomment de volgende regel om de boel in onderhoudsmode te ketzen
//define('MODE', 'ONDERHOUD');

define('DEBUG', 'DEBUG');
if(defined('DEBUG')){
	error_reporting(E_ALL);
}

# default to website mode
# [ WEB, CLI, BOT ]
if (!defined('MODE')) define('MODE', 'WEB');

//alle paden goedzetten.
require_once('include.defines.php');

if (constant('MODE') == 'WEB') {
	# We willen geen sessie-id in de url hebben
	ini_set('session.use_only_cookies', 1);
	session_save_path(SESSION_PATH);

	# wat instellingen
	ini_set('upload_tmp_dir',TMP_PATH);
}

setlocale(LC_ALL, 'nl_NL.utf8');

//standaard templaat voor de C.S.R.-courant
define('COURANT_TEMPLATE', 'courant.tpl');

# wordt gebruikt om pagina's alleen op Confide te laten zien
define('CONFIDE_IP', '145.94.88.238');

# hoe ver van tevoren worden maaltijden getoond?
define('MAALTIJD_LIJST_MAX_TOT', 86400*7*4);
# hoe lang van tevoren mag je iemand anders voor een maaltijd inschrijven?
define('MAALTIJD_PROXY_MAX_TOT', 86400*2);
# wat is het standaard aantal max inschrijvingen voor een maaltijd?
define('MAX_MAALTIJD', 100);
# wat is het aantal te behalen corveepunten per lid per jaar?
define('CORVEEPUNTEN', 11);

# hoeveel dagen van tevoren worden agendapunten standaard getoond?
define('AGENDA_LIJST_DEFAULT_DAGEN', 70);

//verenigingsstatisticus
define('STATISTICUS', '0630' );
//Feut ip voor de rss feed in #csrdelft
define('FEUT_IP', '82.94.188.77');

//is het al weer tijd om overal owee stylesheets aan toe te voegen?
define('OWEE', true);

//stapeltje dingen includeren die toch (bijna) altijd nodig zijn:
require_once 'common.functions.php';
require_once 'lid/loginlid.class.php';
require_once 'mysql.class.php';

switch (constant('MODE')) {
	case 'ONDERHOUD':
		$loginlid = LoginLid::instance();
		if(!$loginlid->hasPermission('P_ADMIN')){
			header('location: '.CSR_ROOT.'/tools/onderhoud.html');
			exit;
		}
	case 'WEB':
		require_once 'simplehtml.class.php';
		require_once 'csrdelft.class.php';
		require_once 'csrubb.class.php';
		require_once 'csrsmarty.class.php';
		require_once 'icon.class.php';

		# N.B. het is van belang dat na het starten van de sessie meteen het databaseobject en het
		# Lid-object worden aangemaakt, omdat die de ingelogde gebruiker controleert, en tevens
		# sess_deleted bugs ondervangt en ip-checks doet
		session_start();
		//database & lid initialiseren...
		$db = MySQL::instance();
		$loginlid = LoginLid::instance();
	break;

	case 'BOT':
	case 'CLI':
        $db = MySQL::instance();
		//TODO: voor bot & cli blijft het nog even $lid ipv $loginlid, nog geen zin om dat allemaal aan te passen.
		$lid = LoginLid::instance();
	break;

	default:
		die("configuratie.include.php:: unsupported MODE");
}

?>
