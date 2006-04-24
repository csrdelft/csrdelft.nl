<?php

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
require_once('class.nieuwscontent.php');
require_once('class.nieuws.php');
$nieuws = new Nieuws($db, $lid);
$nieuwscontent = new NieuwsContent($nieuws);
# Nieuwspagina
if(isset($_POST['titel']) AND isset($_POST['tekst']) AND $nieuws->isNieuwsMod()){
	if(isset($_GET['toevoegen'])){
		if($nieuwscontent->valideerFormulier()){
			$titel=ucfirst($_POST['titel']);
			require_once('bbcode/include.bbcode.php');
			$bbcode_uid=bbnewuid();
			$tekst=bbsave($_POST['tekst'], $bbcode_uid, $db->dbResource());
			$prive=$verborgen=0;
			if(isset($_POST['prive'])){ $prive=1; }
			if(isset($_POST['verborgen'])){ $verborgen=1; }
			//bericht uiteindelijk toevoegen
			if($nieuws->addMessage($titel, $tekst, $bbcode_uid, $prive, $verborgen)){
				//gelukt
				header('location: /nieuws/'); exit;
			}else{
				header('location: /nieuws/'.urlencode('het ging fout')); exit;
			}
		}else{
			//formulier geeft een fout, geef het opnieuw weer
			$nieuwscontent->setActie('toevoegen');
		}
	}else{
		$iBerichtID=(int)$_GET['berichtID'];
		if(isset($_GET['bewerken']) ){
			if($nieuwscontent->valideerFormulier()){
				$titel=ucfirst($_POST['titel']);
				require_once('bbcode/include.bbcode.php');
				$bbcode_uid=bbnewuid();
				$tekst=bbsave($_POST['tekst'], $bbcode_uid, $db->dbResource());
				$prive=$verborgen=0;
				if(isset($_POST['prive'])){ $prive=1; }
				if(isset($_POST['verborgen'])){ $verborgen=1; }
				//bericht uiteindelijk toevoegen
				if($nieuws->editMessage($iBerichtID, $titel, $tekst, $bbcode_uid, $prive, $verborgen)){
					header('location: /nieuws/'.$iBerichtID); exit;
				}else{
					header('location: /nieuws/'.$iBerichtID); exit;
				}
			}else{
				$nieuwscontent->setBerichtID((int)$_GET['berichtID']);
				$nieuwscontent->setActie('bewerken');
			}
		}
	}
}else{
	if(isset($_GET['berichtID'])) {
		$nieuwscontent->setBerichtID((int)$_GET['berichtID']);
		if(isset($_GET['bewerken']) ){
			if($nieuws->isNieuwsMod()){
				$nieuwscontent->setActie('bewerken');
			}
		}else{
			$nieuwscontent->setActie('bericht');
		}
	}else{
		if(isset($_GET['toevoegen'])){
			$nieuwscontent->setActie('toevoegen');
		}
	}
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
$col1->addObject($nieuwscontent);

# Pagina maken met deze twee kolommen
require_once('class.page.php');
$page = new Page();
$page->addColumn($col0);
$page->addColumn($col1);
$page->addTitel('nieuws');

$page->view();

?>
