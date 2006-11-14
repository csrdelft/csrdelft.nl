<?php

	# instellingen & rommeltjes
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
	require_once('class.includer.php');
	$datum = new Includer('', 'datum.php');

	# Het middenstuk
	if ($lid->hasPermission('P_MAIL_POST')) {
		require_once('class.csrmail.php');
		$csrmail = new Csrmail($lid, $db);
		require_once('class.csrmailcontent.php');
		$midden = new CsrmailContent($csrmail);
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if($csrmail->valideerBerichtInvoer($sError)===true){
				$iBerichtID=(int)$_GET['ID'];
				if($iBerichtID==0){
					//nieuw bericht invoeren
					if($csrmail->addBericht($_POST['titel'], $_POST['categorie'], $_POST['bericht'] )){
						$midden->addUserMessage('<h3>Dank u</h3>
							Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.');
					}else{
						$midden->addUserMessage('<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. 
							Probeer opnieuw, of stuur uw bericht in een mail naar pubcie@csrdelft.nl');
					}
				}else{
					//bericht bewerken.
					if($csrmail->bewerkBericht($iBerichtID, $_POST['titel'], $_POST['categorie'], $_POST['bericht'])){
						$midden->addUserMessage('<h3>Dank u</h3>
							Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.');
					}else{
						$midden->addUserMessage('<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. 
							Probeer opnieuw, of stuur uw bericht in een mail naar pubcie@csrdelft.nl');
					}
				}
			}else{
				if(isset($_GET['ID']) AND $_GET['ID']==0){
					$midden->addNewForm($sError);
				}else{
					$midden->addEditForm((int)$_GET['ID'], $sError);
				}
			}
		}else{
			if(isset($_GET['ID'])){
				$iBerichtID=(int)$_GET['ID'];
				if(isset($_GET['verwijder'])){
					//verwijderen
					if($csrmail->verwijderBerichtVoorGebruiker($iBerichtID)){
						$midden->addUserMessage('<h3>Uw bericht is verwijderd.</h3>');
					}else{
						$midden->addUserMessage('<h3>Er ging iets mis!</h3>
							Uw bericht is niet verwijderd. Probeer het a.u.b. nog eens.');
					}
				}
				if(isset($_GET['bewerken'])){
					//bericht bewerken.
					$midden->addEditForm($iBerichtID);
				}
			}
		}
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
	$page->addTitel('PubCie-post berichten toevoegen');
	
	$page->view();
	


?>
