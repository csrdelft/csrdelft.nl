<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# include.config.php
# -------------------------------------------------------------------
# Instellingen van het include_path enzo...
# -------------------------------------------------------------------
#

//uncomment de volgende regel om de boel in onderhoudsmode te ketzen
//define('MODE', 'ONDERHOUD');

error_reporting(E_ALL);

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

//standaard templaat voor de csrmail
define('CSRMAIL_TEMPLATE', 'csrmail.tpl');

# wordt gebruikt om pagina's alleen op Confide te laten zien
define('CONFIDE_IP', '82.92.101.131:145.94.37.96');

# hoe ver van tevoren worden maaltijden getoond?
define('MAALTIJD_LIJST_MAX_TOT', 86400*7*4);
# hoe lang van tevoren mag je iemand anders voor een maaltijd inschrijven?
define('MAALTIJD_PROXY_MAX_TOT', 86400*2);
# wat is het standaard aantal max inschrijvingen voor een maaltijd?
define('MAX_MAALTIJD', 100);

# hoeveel dagen van tevoren worden agendapunten standaard getoond?
define('AGENDA_LIJST_DEFAULT_DAGEN', 70);

//verenigingsstatisticus
define('STATISTICUS', '0430' );
//Feut ip voor de rss feed in #csrdelft
define('FEUT_IP', '82.94.188.77');

//stapeltje dingen includeren die toch (bijna) altijd nodig zijn:
require_once('include.common.php');
require_once('class.lid.php');
require_once('class.mysql.php');

switch (constant('MODE')) {
	case 'ONDERHOUD':
		$lid = Lid::get_lid();
		if(!$lid->hasPermission('P_ADMIN')){
			header('location: '.CSR_ROOT.'/tools/onderhoud.html');
			exit;
		}
	case 'WEB':
		require_once('class.simplehtml.php');
		require_once('class.kolom.php');
		require_once('class.includer.php');
		require_once('class.stringincluder.php');
		require_once('class.csrdelft.php');
		require_once('class.csrubb.php');
		require_once('class.csrsmarty.php');

		# N.B. het is van belang dat na het starten van de sessie meteen het databaseobject en het
		# Lid-object worden aangemaakt, omdat die de ingelogde gebruiker controleert, en tevens
		# sess_deleted bugs ondervangt en ip-checks doet
		session_start();
		//database & lid initialiseren...
		$db = MySQL::get_mysql();
		$lid = Lid::get_lid();
	break;
	
	case 'BOT':
	case 'CLI':
        $db = MySQL::get_mysql();
        $lid = Lid::get_lid();
	break;

	default:
		die("include.config.php:: unsupported MODE");
}

?>
