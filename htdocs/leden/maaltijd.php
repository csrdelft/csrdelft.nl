<?php

main();
exit;


function main() {
	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	session_start();
	$db = new MySQL();
	$lid = new Lid($db);

	### Pagina-onderdelen ###

	# menu's
	require_once('class.dbmenu.php');
	$homemenu = new DBMenu('home', $lid, $db);
	$infomenu = new DBMenu('info', $lid, $db);
	if ($lid->isLoggedIn()) $ledenmenu = new DBMenu('leden', $lid, $db);

	require_once('class.simplehtml.php');
	require_once('class.hok.php');
	$homemenuhok = new Hok($homemenu->getMenuTitel(), $homemenu);
	$infomenuhok = new Hok($infomenu->getMenuTitel(), $infomenu);
	if ($lid->isLoggedIn()) $ledenmenuhok = new Hok($ledenmenu->getMenuTitel(), $ledenmenu);

	require_once('class.loginform.php');
	$loginform = new LoginForm($lid);
	$loginhok = new Hok('Ledenlogin', $loginform);

	# Datum
	require_once('class.includer.php');
	$datum = new Includer('', 'datum.php');

	# MaaltijdenSysteem
	require_once('class.maaltrack.php');
	require_once('class.maaltijd.php');
	$maaltrack = new MaalTrack($lid, $db);

	# Moeten er acties uitgevoerd worden?
	if (isset($_POST['a'])) $action = $_POST['a'];
	elseif (isset($_GET['a'])) $action = $_GET['a'];
	else $action = 'none';
	
	# volgende code gejat uit profiel.php:
	# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
	# te kunnen laden in plaats van de profiel pagina omdat er geen
	# toegang wordt verleend voor de actie die gevraagd wordt.
	$error = 0;
	# 0 -> gaat goed
	# 1 -> mag niet, foutpagina afbeelden
	# 2 -> er treden (vorm)fouten op in bijv de invoer.

	# controleren of we wel mogen doen wat er gevraagd wordt...
	switch ($action) {
		case 'none':
		case 'addabo':
		case 'delabo':
		case 'aan':
		case 'af':
			if ( !$lid->hasPermission('P_MAAL_IK') ) $error = 1;
			break;
		default:
			# geen geklooi met andere waarden
			$error = 1;
	}

	# als er geen error is, dan kunnen we de actie uit gaan voeren
	if ($error == 0) switch($action) {
		case 'addabo':
			# kijk of er een abo is opgegeven
			if (isset($_POST['abo'])) $abo = $_POST['abo'];
			elseif (isset($_GET['abo'])) $abo = $_GET['abo'];
			else $abo = '';
			$error = ($maaltrack->addabo($abo)) ? 0 : 2;
			if ($error == 0) {
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
			break;
		case 'delabo':
			# kijk of er een abo is opgegeven
			if (isset($_POST['abo'])) $abo = $_POST['abo'];
			elseif (isset($_GET['abo'])) $abo = $_GET['abo'];
			else $abo = '';
			$error = ($maaltrack->delabo($abo)) ? 0 : 2;
			if ($error == 0) {
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
			break;
		case 'aan':
			# kijk of een maaltijd is opgegeven
			if (isset($_POST['m'])) $m = $_POST['m'];
			elseif (isset($_GET['m'])) $m = $_GET['m'];
			else $m = '';
			# kijk of er extra permissies nodig zijn als we iemand anders
			# aan willen melden
			if (isset($_POST['uid'])) $uid = $_POST['uid'];
			elseif (isset($_GET['uid'])) $uid = $_GET['uid'];
			else $uid = '';
			if ($uid != '' and $uid != $lid->getUid()
			    and !$lid->hasPermission('P_MAAL_WIJ') ) $error = 1;
			# ga maar proberen dan...
			$error = ($maaltrack->aanmelden($m, $uid)) ? 0 : 2;
			if ($error == 0) {
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
			break;
		case 'af':
			# kijk of een maaltijd is opgegeven
			if (isset($_POST['m'])) $m = $_POST['m'];
			elseif (isset($_GET['m'])) $m = $_GET['m'];
			else $m = '';
			# kijk of er extra permissies nodig zijn als we iemand anders
			# af willen melden
			if (isset($_POST['uid'])) $uid = $_POST['uid'];
			elseif (isset($_GET['uid'])) $uid = $_GET['uid'];
			else $uid = '';
			if ($uid != '' and $uid != $lid->getUid()
			    and !$lid->hasPermission('P_MAAL_WIJ') ) $error = 1;
			# ga maar proberen dan...
			$error = ($maaltrack->afmelden($m, $uid)) ? 0 : 2;
			if ($error == 0) {
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
			break;
	}

	
	# De pagina opbouwen, met profiel, of met foutmelding
	if ($error == 0  or $error == 2) {
		# Het middenstuk
		require_once('class.maaltijdcontent.php');
		$midden = new MaaltijdContent($lid, $maaltrack);
	} else {
		# geen rechten
		require_once('class.includer.php');
		$midden = new Includer('', 'maaltijd-niet-ingelogged.html');
	}

	### Kolommen vullen ###
	require_once('class.column.php');
	$col0 = new Column(COLUMN_MENU);
	$col0->addObject($homemenuhok);
	$col0->addObject($infomenuhok);
	if ($lid->isLoggedIn()) $col0->addObject($ledenmenuhok);
	$col0->addObject($loginhok);
	$col0->addObject($datum);

	$col1 = new Column(COLUMN_MIDDENRECHTS);
	$col1->addObject($midden);

	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);

	$page->view();
	
}

?>
