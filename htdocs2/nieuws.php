<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


# Het middenstuk
require_once('class.nieuwscontent.php');
require_once('class.nieuws.php');
$nieuws = new Nieuws($db, $lid);

//aantal berichten op deze pagina lekker hoog, soort archief/overzicht.
$nieuws->setAantal(25);
$nieuwscontent = new NieuwsContent($nieuws);

$bbcode_uid=bbnewuid();
define('REFRESH', CSR_ROOT.'nieuws/');

//plaetje verwerken:
if(isset($_FILES['plaatje'], $_GET['berichtID']) AND $nieuws->isNieuwsMod() AND $_FILES['plaatje']['name']!=''){
	$info=getimagesize($_FILES['plaatje']['tmp_name']); 
	$berichtID=(int)$_GET['berichtID'];
	//verhouding controleren
	if(($info[0]/$info[1])==60/100){
		$plaatje=$_FILES['plaatje']['name'];
		if(move_uploaded_file($_FILES['plaatje']['tmp_name'], PICS_PATH.'/nieuws/'.$plaatje )){
			if($info[0]!=60){ //te groot, verkleinen.
				$nieuws->resize_plaatje(PICS_PATH.'/nieuws/'.$plaatje);
			}
			if(!$nieuws->setPlaatje($berichtID, $plaatje)){
				$nieuwscontent->setError('Afbeelding toevoegen mislukt.<br />');
			}
			chmod(PICS_PATH.'/nieuws/'.$plaatje, 0644);
		}else{
			$nieuwscontent->setError('Afbeelding verplaatsen is mislukt.<br />');
		}
	}else{
		$nieuwscontent->setError('Afbeelding is niet in de juiste verhouding.<br />');
	}
}
//plaatje verwijderen
if(isset($_GET['berichtID'], $_GET['plaatje']) AND $_GET['plaatje']=='delete' AND $nieuws->isNieuwsMod()){
	$nieuws->setPlaatje((int)$_GET['berichtID'], '');
	header('location: '.REFRESH); exit;
}	
# Nieuwspagina
if(isset($_POST['titel'], $_POST['tekst']) AND $nieuws->isNieuwsMod()){
	if(isset($_GET['toevoegen'])){
		if($nieuwscontent->valideerFormulier()){
			$tekst=bbsave($_POST['tekst'], $bbcode_uid, $db->dbResource());
			$prive=$verborgen=0;
			if(isset($_POST['prive'])){ $prive=1; }
			if(isset($_POST['verborgen'])){ $verborgen=1; }
			//bericht uiteindelijk toevoegen
			if($nieuws->addMessage(ucfirst($_POST['titel']), $tekst, $bbcode_uid, $prive, $verborgen)){
				header('location: '.REFRESH); exit;
			}else{
				$nieuwscontent->setError("Query mislukt");
			}
		}else{
			//formulier geeft een fout, geef het opnieuw weer
			$nieuwscontent->setActie('toevoegen');
		}
	}else{
		$iBerichtID=(int)$_GET['berichtID'];
		if(isset($_GET['bewerken']) ){
			if($nieuwscontent->valideerFormulier()){
				$tekst=bbsave($_POST['tekst'], $bbcode_uid, $db->dbResource());
				$prive=$verborgen=0;
				if(isset($_POST['prive'])){ $prive=1; }
				if(isset($_POST['verborgen'])){ $verborgen=1; }
				//bericht uiteindelijk toevoegen
				if($nieuws->editMessage($iBerichtID, ucfirst($_POST['titel']), $tekst, $bbcode_uid, $prive, $verborgen)){
					header('location: '.REFRESH.$iBerichtID); exit;
				}else{
					header('location: '.REFRESH.$iBerichtID); exit;
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
					header('location: '.REFRESH); exit;
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
