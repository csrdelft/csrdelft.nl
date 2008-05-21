<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
require_once('class.nieuwscontent.php');
require_once('class.nieuws.php');
$nieuws = new Nieuws();

$nieuws->setAantalTopBerichten(3);	// Het aantal top-berichten dat op de Mededelingenpagina wordt weergegeven.
$nieuws->setTopBerichtenSpeling(3); // Het aantal éxtra berichten die (bijv.) de PubCie-P kan markeren als top-
									// bericht. Zo kan ook de top-berichten-weergave van externen gereguleerd
									// worden.
$nieuws->setStandaardRank(255);
$nieuwscontent = new NieuwsContent($nieuws);

define('REFRESH', CSR_ROOT.'actueel/mededelingen/');

//plaetje verwerken:
if(isset($_FILES['plaatje'], $_GET['berichtID']) AND $nieuws->isNieuwsMod() AND $_FILES['plaatje']['name']!=''){
	$info=getimagesize($_FILES['plaatje']['tmp_name']); 
	$berichtID=(int)$_GET['berichtID'];
	//verhouding controleren
	if(($info[0]/$info[1])==1){
		$plaatje=$_FILES['plaatje']['name'];
		if(move_uploaded_file($_FILES['plaatje']['tmp_name'], PICS_PATH.'/nieuws/'.$plaatje )){
			if($info[0]!=200){ //te groot, verkleinen.
				$nieuws->resize_plaatje(PICS_PATH.'/nieuws/'.$plaatje);
			}
			if(!$nieuws->setPlaatje($berichtID, $plaatje)){
				$nieuwscontent->setMelding('Afbeelding toevoegen mislukt.<br />');
			}
			chmod(PICS_PATH.'/nieuws/'.$plaatje, 0644);
		}else{
			$nieuwscontent->setMelding('Afbeelding verplaatsen is mislukt.<br />');
		}
	}else{
		$nieuwscontent->setMelding('Afbeelding is niet in de juiste verhouding.<br />');
	}
}
//plaatje verwijderen
if(isset($_GET['berichtID'], $_GET['plaatje']) AND $_GET['plaatje']=='delete' AND $nieuws->isNieuwsMod()){
	$nieuws->setPlaatje((int)$_GET['berichtID'], '');
	header('location: '.REFRESH); exit;
}	
# Nieuwspagina
if(isset($_POST['titel'], $_POST['tekst'], $_POST['categorie'], $_POST['rank']) AND $nieuws->isNieuwsMod()){
	if(isset($_GET['toevoegen'])){
		if($nieuwscontent->valideerFormulier()){
			$tekst=$db->escape($_POST['tekst']);
			$categorie=(int)$_POST['categorie'];
			$rank=(int)$_POST['rank'];
			$prive=$verborgen=0;
			if(isset($_POST['prive'])){ $prive=1; }
			if(isset($_POST['verborgen'])){ $verborgen=1; }
			//bericht uiteindelijk toevoegen
			if($nieuws->addMessage(ucfirst($_POST['titel']), $tekst, $categorie, $rank, $prive, $verborgen)){
				header('location: '.REFRESH); exit;
			}else{
				$nieuwscontent->setMelding("Query mislukt ");
			}
		}else{
			//formulier geeft een fout, geef het opnieuw weer
			$nieuwscontent->setActie('toevoegen');
		}
	}else{
		$iBerichtID=(int)$_GET['berichtID'];
		if(isset($_GET['bewerken']) ){
			if($nieuwscontent->valideerFormulier()){
				$tekst=$db->escape($_POST['tekst']);
				$categorie=(int)$_POST['categorie'];
				$rank=(int)$_POST['rank'];
				$prive=$verborgen=0;
				if(isset($_POST['prive'])){ $prive=1; }
				if(isset($_POST['verborgen'])){ $verborgen=1; }
				//bericht uiteindelijk toevoegen
				$nieuws->editMessage($iBerichtID, ucfirst($_POST['titel']), $tekst, $categorie, $rank, $prive, $verborgen);
				header('location: '.REFRESH.$iBerichtID); exit;
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
		}elseif(isset($_GET['beheer'])){
			$nieuwscontent->setActie('beheer');
		}
	}
}

$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($nieuwscontent);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
