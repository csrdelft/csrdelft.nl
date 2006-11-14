<?php

# voorkomen globale variabelen
main();
exit;

function main() {

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

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

	# Alpha cursus
	$alpha = new Includer('', 'alpha.html');

	# Het middenstuk
	require_once('class.includer.php');
	$thuis = new Includer('', 'thuis.html');
	$thuis2 = new Includer('', 'banners.html');

	require_once('class.nieuwscontent.php');
	require_once('class.nieuws.php');
	$nieuws = new Nieuws($db, $lid);
	$nc = new NieuwsContent($nieuws);

	

	### Kolommen vullen ###
	require_once('class.column.php');
	$col0 = new Column(COLUMN_MENU);
	$col0->addObject($homemenuhok);
	$col0->addObject($infomenuhok);
	if ($lid->isLoggedIn()) $col0->addObject($ledenmenuhok);
	$col0->addObject($loginhok);
	$col0->addObject($datum);
	$col0->addObject($alpha);

	$col1 = new Column(COLUMN_MIDDENRECHTS);
	$col1->addObject($thuis);
	$col1->addObject($nc);
	$col1->addObject($thuis2);
	
	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);
	$page->addTitel('thuis');

	$page->view();
	
}

?>
