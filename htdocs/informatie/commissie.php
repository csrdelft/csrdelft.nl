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

	# Het middenstuk
	require_once('class.commissie.php');
	$commissie = new Commissie($db, $lid);

	if(isset($_GET['cie'])){
		$commissie->loadCommissie($_GET['cie']);
			
		//enkel beheerdingen doen als het met id's gebeurt.
		if(preg_match('/^\d+$/', $_GET['cie']) AND $commissie->magBewerken($_GET['cie'])){ 
			$iCieID=(int)$_GET['cie'];
			if(isset($_GET['verwijderen']) AND isset($_GET['uid'])){
				$commissie->verwijderCieLid($iCieID, $_GET['uid']);
				header('location: http://csrdelft.nl/informatie/commissie/'.$iCieID);
				exit;
			//alleen nieuwe leden erin gaan stoppen als beide arrays erzijn, en even veel elementen hebben zijn.
			}elseif(isset($_POST['naam']) AND isset($_POST['functie']) AND
				is_array($_POST['naam']) AND is_array($_POST['functie']) AND
				count($_POST['naam'])==count($_POST['functie']) ){
				//nieuwe commissieleden erin stoppen.
				for($iTeller=0; $iTeller<count($_POST['naam']); $iTeller++){
					$commissie->addCieLid($iCieID, $_POST['naam'][$iTeller], $_POST['functie'][$iTeller]);
				}
				header('location: http://csrdelft.nl/informatie/commissie/'.$iCieID);
				exit;
			}	
		}

		require_once('class.commissiecontent.php');
		$middenvak = new CommissieContent($commissie, $lid);
		if(preg_match('/^\d+$/', $_GET['cie'])){
			$titel=$commissie->getNaam($_GET['cie']);
		}else{
			$titel='commissie: '.mb_htmlentities($_GET['cie']);
		}
	} else {
		require_once('class.cieoverzichtcontent.php');
		$middenvak = new CieOverzichtContent($commissie, $lid);
		$titel='commissieoverzicht';
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
	$col1->addObject($middenvak);

	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);
	$page->addTitel($titel);
	
	$page->view();
	
}

?>
