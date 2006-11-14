<?php

	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


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
	require_once('class.simplehtml.php');
	$niets = new SimpleHTML();

	### Kolommen vullen ###
	require_once('class.column.php');
	$col0 = new Column(COLUMN_MENU);
	$col0->addObject($homemenuhok);
	$col0->addObject($infomenuhok);
	if ($lid->isLoggedIn()) $col0->addObject($ledenmenuhok);
	$col0->addObject($loginhok);
	$col0->addObject($datum);

	$col1 = new Column(COLUMN_MIDDENRECHTS);
	$col1->addObject($niets);

	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);

	$page->view();



?>
