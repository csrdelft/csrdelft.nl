<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


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
				header('location: '.CSR_ROOT.'nieuws/'); exit;
			}else{
				header('location: '.CSR_ROOT.'nieuws/'.urlencode('het ging fout')); exit;
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
					header('location: '.CSR_ROOT.'nieuws/'.$iBerichtID); exit;
				}else{
					header('location: '.CSR_ROOT.'nieuws/'.$iBerichtID); exit;
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
		}elseif(isset($_GET['verwijderen'])){
			$iBerichtID=(int)$_GET['berichtID'];
			if($nieuws->isNieuwsMod()){
				if($nieuws->deleteMessage($iBerichtID)){
					header('location: '.CSR_ROOT.'nieuws/'); exit;
				}
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

$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($nieuwscontent, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
