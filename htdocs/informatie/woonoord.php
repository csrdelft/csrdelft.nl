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
	require_once('class.woonoord.php');
	require_once('class.woonoordcontent.php');
	$woonoord = new Woonoord($db, $lid);
	if(isset($_GET['woonoordid'])){
    $iWoonoordID=(int)$_GET['woonoordid'];
    if(isset($_GET['verwijderen']) AND isset($_GET['uid']) AND preg_match('/^\w{4}$/', $_GET['uid']) AND 
    	($woonoord->magBewerken($iWoonoordID) OR $lid->hasPermission('P_LEDEN_MOD'))){
      //een bewoner verwijderen uit een woonoord
      $woonoord->delBewoner($iWoonoordID, $_GET['uid']);
      header('location: http://csrdelft.nl/informatie/woonoord.php');
      exit;
    }elseif( isset($_POST['rawBewoners']) AND $woonoord->magBewerken($iWoonoordID)){
    	$aBewoners=namen2uid($_POST['rawBewoners'], $lid);
       if(is_array($aBewoners) AND count($aBewoners)>0){
      	$iSuccesvol=0;
      	foreach($aBewoners as $aBewoner){
      		if(isset($aBewoner['uid'])){
      			$woonoord->addBewoner($iWoonoordID, $aBewoner['uid']);
      			$iSuccesvol++;
      		}
      	}
      	if($iSuccesvol==count($aBewoners)){
      		header('location: http://csrdelft.nl/informatie/woonoord.php#'.$iWoonoordID);
      		exit;
      	}
      }	
    }elseif(isset($_POST['bewoners']) AND is_array($_POST['bewoners']) AND $woonoord->magBewerken($iWoonoordID)){
      foreach($_POST['bewoners'] as $bewoner){
      	if(preg_match('/^\w{4}$/', $bewoner)){
      		$woonoord->addBewoner($iWoonoordID, $bewoner);
      	}
      }
      header('location: http://csrdelft.nl/informatie/woonoord.php');
      exit;
    }  
      
  }
  $midden = new WoonoordContent($woonoord, $lid);

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
	$page->addTitel('woonoorden');

	$page->view();
	
}

?>