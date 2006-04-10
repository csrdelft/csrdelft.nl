<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

# /leden/profiel.php

# prevent global namespace poisoning
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
	if ($lid->hasPermission('P_LOGGED_IN')) $ledenmenu = new DBMenu('leden', $lid, $db);

	require_once('class.simplehtml.php');
	require_once('class.hok.php');
	$homemenuhok = new Hok($homemenu->getMenuTitel(), $homemenu);
	$infomenuhok = new Hok($infomenu->getMenuTitel(), $infomenu);
	if ($lid->hasPermission('P_LOGGED_IN')) $ledenmenuhok = new Hok($ledenmenu->getMenuTitel(), $ledenmenu);

	require_once('class.loginform.php');
	$loginform = new LoginForm($lid);
	$loginhok = new Hok('Ledenlogin', $loginform);

	# Datum
	require_once('class.includer.php');
	$datum = new Includer('', 'datum.php');

	# Profiel bekijken
	# met P_LOGGED_IN mag een gebruiker zijn eigen profiel bekijken
	# met P_LEDEN_READ mag een gebruiker profielen van andere leden bekijken
	# met P_PROFIEL_EDIT mag een gebruiker zijn eigen profiel wijzigen
	# met P_LEDEN_EDIT mag een gebruiker profielen van anderen wijzigen

	# ophalen uid die meegegeven is
	# NB uid zit altijd in de URL als het niet de uid van de gebruiker zelf is!
	if (isset($_GET['uid'])) $uid = $_GET['uid'];
	else $uid = $lid->getUid();

	# we gaan dingen met acties doen...
	require_once("class.state.php");
	# maak een Status aan, dit ene object wordt overal geraadpleegd. met
	# het veranderen van de status binnen dit object verandert de hele
	# pagina. State bevat een URL die naar de pagina zelf verwijst en waar
	# alleen nog maar a=<actie> aan toegevoegd hoeft te worden.
	# daarnaast bevat het dus de actie waar we mee bezig zijn, en die
	# in de content-klassen bepaalt wat er wel of niet wordt getoond
	$state = new State('none', "{$_SERVER['PHP_SELF']}?uid={$uid}");

	# zijn we met beheer bezig?
	if (isset($_POST['a'])) $action = $_POST['a'];
	elseif (isset($_GET['a'])) $action = $_GET['a'];
	else $action = 'none';

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
			# Eigen profiel bekijken kan met P_LOGGED_IN, profiel van anderen
			# bekijken kan met P_LEDEN_READ en met P_OUDLEDEN_READ
			# oudleden kunnen dan ook leden bekijken en vice-versa, maar enkel
			# als ze uid's kennen.
			if ( !($lid->hasPermission('P_LOGGED_IN') and $uid == $lid->getUid()) and 
				!($lid->hasPermission('P_LEDEN_READ') or $lid->hasPermission('P_OUDLEDEN_READ') )){
				$error = 1;
			}
			break;
		case 'edit':
		case 'save':
			# wijzigen van spullen kan met P_PROFIEL_EDIT als de gevraagde
			# gebruiker dezelfde is als de ingelogde gebruiker, of met
			# P_LEDEN_EDIT
			
			# FIXME: duidelijkere opzet van statement hieronder. dit is te wazig
			if ( !($lid->hasPermission('P_LOGGED_IN') and
			       $lid->hasPermission('P_PROFIEL_EDIT') and
			       $uid == $lid->getUid()
			      ) and
			      !($lid->hasPermission('P_LEDEN_EDIT')) )
				$error = 1;
			break;
		default:
			# geen geklooi met andere waarden
			$error = 1;
	}

	# als er geen error is, dan kunnen we de actie uit gaan voeren
	if ($error == 0) switch($action) {
		case 'none':
			# profiel inladen, als dat niet lukt dan mag het niet
			if (!$lid->loadSqlTmpProfile($uid)) $error = 1;
			break;
		case 'edit':
			# profiel inladen, als dat niet lukt dan mag het niet
			if ($lid->loadSqlTmpProfile($uid)) $state->setMyState('edit'); # zodat editvakken getoond worden
			else $error = 1;
			break;
		case 'save':
			# profiel inladen uit db, als dat niet lukt dan mag het niet
			if (!$lid->loadSqlTmpProfile($uid)) {
				$error = 1;
				break;
			}

			# profiel inladen uit POST, als dat niet lukt kan het zijn dat...
			# $error = 1 -> we een 'dat mag niet' pagina gaan afbeelden
			# $error = 2 -> doorgaan, en naar edit-mode, er moeten eerst fouten opgelost worden
			$error = $lid->loadPOSTTmpProfile();
			switch ($error) {
				case 0:
					# alle invoer was juist, wijzigingen doorvoeren.
					# deze functie doet:
					
					# - maak een xml bestandje met de wijzigingen.
					#$lid->diff_to_xml();
					
					# - wijzigingen in SQL opslaan
					$lid->diff_to_sql();
					
					# - wijzigingen in LDAP opslaan
					#$lid->diff_to_ldap();
					
					# - wijzigingen doorgeven aan de Vice-Abactis
					#$lid->diff_to_vab();
					
					# om te voorkomen dat een refresh opnieuw een submit doet
					$myurl = $state->getMyUrl();
					header("Location: {$myurl}");
					exit;
				case 2:
					# er zaten fouten in de invoer, $lid weet welke fouten en
					# profielcontent zal die afbeelden
					$state->setMyState('edit'); 
					break;
				case 1:
					# geen-toegang pagina wordt hieronder ingevuld
					break;
			}
			break;
	}

	# De pagina opbouwen, met profiel, of met foutmelding
	switch ($error) {
		case 0:
		case 2:
			# Het middenstuk
			require_once('class.profielcontent.php');
			$midden = new ProfielContent($lid, $state);
			break;
		default:
			# geen rechten
			require_once('class.includer.php');
			$midden = new Includer('', 'geentoegang.html');
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
