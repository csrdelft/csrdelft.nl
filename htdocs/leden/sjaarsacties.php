<?php
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


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
if ($lid->isLoggedIn()) $ledenmenuhok = new Hok($ledenmenu->getMenuTitel(), $ledenmenu);

require_once('class.loginform.php');
$loginform = new LoginForm($lid);
$loginhok = new Hok('Ledenlogin', $loginform);

# Datum
$datum = new Includer('', 'datum.php');

# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.sjaarsactie.php');
	$sjaarsactie = new Sjaarsactie($lid, $db);
	
	//nieuwe aanmelding voor een bestaande sjaarsactie
	if(isset($_GET['actieID'], $_GET['aanmelden']) AND 
		$sjaarsactie->isSjaars() AND !$sjaarsactie->isVol((int)$_GET['actieID'])){
		//nieuw persoon aanmelden voor een actie
		$sjaarsactie->meldAan((int)$_GET['actieID'], $lid->getUid());
		header('location: http://csrdelft.nl/leden/sjaarsacties/');
		exit;
	}
	
	//nieuwe sjaarsactie aanmelden
	if(isset($_POST['verzenden']) AND $sjaarsactie->validateSjaarsactie() AND !$sjaarsactie->isSjaars()){
		$sjaarsactie->newSjaarsactie($_POST['actieNaam'], $_POST['beschrijving'], $_POST['limiet']);
		header('location: http://csrdelft.nl/leden/sjaarsacties/');
		exit;
	}
		
	require_once('class.sjaarsactiecontent.php');
	$midden = new SjaarsactieContent($sjaarsactie);
} else {
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
$page->addTitel('Sjaarsacties 2006');

$page->view();


?>
