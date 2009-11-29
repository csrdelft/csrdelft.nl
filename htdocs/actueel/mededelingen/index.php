<?php
require_once('include.config.php');

if(!LoginLid::instance()->hasPermission('P_NEWS_MOD')){ //TODO: gehele IF weghalen.
	header('location: '.CSR_ROOT);
	exit;
}

$mededelingId=0;
if(isset($_GET['mededelingId'])){
	$mededelingId=(int)$_GET['mededelingId'];
}

$actie='default';
if(isset($_GET['actie'])){
	$actie=$_GET['actie'];
}

if(isset($_GET['pagina'])){
	$pagina=(int)$_GET['pagina'];
}

require_once('mededelingen/class.mededeling.php');
require_once('mededelingen/class.mededelingcontent.php');

define('MEDEDELINGEN_ROOT', CSR_ROOT.'actueel/mededelingen/');

switch($actie){
	case 'verwijderen':
		if(!Mededeling::magToevoegen()){
			header('location: '.CSR_ROOT);
			exit;
		}
		if($mededelingId>0){
			$mededeling=new Mededeling($mededelingId);
			if(Mededeling::isModerator() OR $mededeling->getUid()==LoginLid::instance()->getUid()){
				$verwijderd=$mededeling->delete();
				if($verwijderd===false){
					$_SESSION['melding']='Het verwijderen is mislukt.';
				}else{
					$_SESSION['melding']='De mededeling is succesvol verwijderd.';
				}
			}else{ // Dit lid mag deze mededeling helemaal niet verwijderen!
				header('location: '.CSR_ROOT);
				exit;
			}
		}
		require_once('mededelingen/class.mededelingencontent.php');
		$content=new MededelingenContent(0);
		// De eerste pagina laden.
		$content->setPaginaNummer(1);
	break; 

	case 'bewerken':
		if(!Mededeling::magToevoegen()){
			header('location: '.CSR_ROOT);
			exit;
		}
		$_SESSION['melding']='';
		if(	isset($_POST['titel'],$_POST['tekst'],$_POST['categorie']) ){
			// The user is editing an existing Mededeling or tried adding a new one.
			// Get properties from $_POST.
			$mededelingProperties=array();
			$mededelingProperties['id']=		$mededelingId;
			$mededelingProperties['titel']=		$_POST['titel'];
			$mededelingProperties['tekst']=		$_POST['tekst'];
			$mededelingProperties['datum']=		getDateTime();
			$mededelingProperties['uid']=		LoginLid::instance()->getUid();
			if(isset($_POST['prioriteit'])){
				$mededelingProperties['prioriteit']=		(int)$_POST['prioriteit'];
			}
			$mededelingProperties['doelgroep']=	$_POST['doelgroep'];
			if(!Mededeling::isModerator()){
				$mededelingProperties['zichtbaarheid']='wacht_goedkeuring';
			}else{
				$mededelingProperties['zichtbaarheid']=isset($_POST['verborgen']) ? 'onzichtbaar' : 'zichtbaar';
			}
			$mededelingProperties['categorie']=	(int)$_POST['categorie'];

			$allOK=true; // This variable is set to false if there is an error.

			// Special treatment for the picture.
			$mededelingProperties['plaatje']='';
			if(isset($_FILES['plaatje']) AND $_FILES['plaatje']['error']==UPLOAD_ERR_OK){ // If uploading succeedded.
				$info=getimagesize($_FILES['plaatje']['tmp_name']);
				if(($info[0]/$info[1])==1){ // If the ratio is fine (1:1).
					$pictureFilename=$_FILES['plaatje']['name'];
					$pictureFullPath=PICS_PATH.'/nieuws/'.$pictureFilename; // TODO: change nieuws to mededelingen
					if( move_uploaded_file($_FILES['plaatje']['tmp_name'], $pictureFullPath)!==false ){
						$mededelingProperties['plaatje']=$pictureFilename;
						if($info[0]!=200){ // Too big, resize it.
							resize_plaatje($pictureFullPath);
						}
						chmod($pictureFullPath, 0644);
					}else{
						$_SESSION['melding'].='Plaatje verplaatsen is mislukt.<br />';
						$allOK=false;
					}
				}else{
					$_SESSION['melding'].='Plaatje is niet in de juiste verhouding.<br />';
					$allOK=false;
				}
			}
			
			// Check if all values appear to be OK.
			$tijdelijkeMededeling=$mededelingId>0 ? new Mededeling($mededelingId) : null;
			if(strlen($mededelingProperties['titel'])<2){
				$_SESSION['melding'].='Het veld <b>Titel</b> moet minstens 2 tekens bevatten.<br />';
				$allOK=false;
			}
			if(strlen($mededelingProperties['tekst'])<5){
				$_SESSION['melding'].='Het veld <b>Tekst</b> moet minstens 5 tekens bevatten.<br />';
				$allOK=false;
			}
			
			// Check prioriteit.
			$prioriteitIsOngeldig=true;
			if(isset($mededelingProperties['prioriteit'])){
				$prioriteitIsOngeldig=(array_search($mededelingProperties['prioriteit'],array_keys(Mededeling::getPrioriteiten())) === false);
			}
			// Indien de gebruiker geen moderator is OF de prioriteit ongeldig is.
			if(!Mededeling::isModerator() OR $prioriteitIsOngeldig){
				if($tijdelijkeMededeling!==null){ // We bewerken, dus huidige prioriteit behouden.
					$mededelingProperties['prioriteit']=$tijdelijkeMededeling->getPrioriteit();
				}else{ // We voegen toe, dus default prioriteit gebruiken.
					$mededelingProperties['prioriteit']=Mededeling::defaultPrioriteit;
				}
			}
			
			// Check doelgroep.
			if(array_search($mededelingProperties['doelgroep'], Mededeling::getDoelgroepen())===false){
				$_SESSION['melding'].='De doelgroep is ongeldig.<br />';
				$allOK=false;
			}
			
			// Check categorie.
			$categorieValid=false;
			foreach(MededelingCategorie::getCategorieen() as $categorie){
				$hetIsDeze=($mededelingProperties['categorie']==$categorie->getId());
				$categorieOnveranderd=($tijdelijkeMededeling!==null AND $tijdelijkeMededeling->getCategorieId()==$mededelingProperties['categorie']);
				if( $hetIsDeze AND ($categorie->magUitbreiden() OR $categorieOnveranderd) ){
					$categorieValid=true;
				}
			}
			if(	!$categorieValid ){
				$mededelingProperties['categorie']=null;
				$_SESSION['melding'].='De categorie is ongeldig.<br />';
				$allOK=false;
			}
			// Check picture.
			if(empty($mededelingProperties['plaatje']) AND $mededelingId==0){ // If there's no new picture, while there should be.
				$errorNumber=$_FILES['plaatje']['error'];
				if($errorNumber==UPLOAD_ERR_NO_FILE){ // If there was no file being uploaded at all. 
					$_SESSION['melding'].='Het toevoegen van een plaatje is verplicht.<br />';
					$allOK=false;
				}else if($errorNumber!=UPLOAD_ERR_OK){
					// Uploading the picture failed.
					$allOK=false;
				}
				// The last possibility, $errorNumber==UPLOAD_ERR_OK is being issued above, where the picture
				// is being moved.
			}
			
			$mededeling=new Mededeling($mededelingProperties);
			if($allOK){
				// Save the mededeling to the database. (Either via UPDATE or INSERT).
				$realId=$mededeling->save();
				if($realId==-1) // If something went wrong, just go to the main page.
					$realId='';
				//TODO: Melding weergeven dat er iets toegevoegd is (?)
				header('location: '.MEDEDELINGEN_ROOT.$realId); exit;
			}
		}else{ // User is going to edit an existing Mededeling or fill in an empty form.
			$mededeling=new Mededeling($mededelingId);
		}
		
		// Controleren of de gebruiker deze mededeling wel mag bewerken.
		if($mededelingId>0 AND !$mededeling->magBewerken()){ // Moet dit niet eerder gebeuren?
			header('location: '.CSR_ROOT);	// Misschien melding weergeven en terug gaan naar
											// de mededelingenpagina? 
			exit;
		}
		$content=new MededelingContent($mededeling);
	break; 

	default:
		require_once('mededelingen/class.mededelingencontent.php');
		$content=new MededelingenContent($mededelingId);
		if(isset($pagina)){	// Als de gebruiker een pagina opvraagt.
			$content->setPaginaNummer($pagina);
		}else if($mededelingId==0){	// Als de gebruiker GEEN pagina opvraagt en ook geen mededeling.
			$content->setPaginaNummer(1);
		}
	break;
}

$page=new csrdelft($content);
$page->addStylesheet('mededelingen.css');
$page->view();

function resize_plaatje($file) {
	list($owdt,$ohgt,$otype)=@getimagesize($file);
	switch($otype) {
		case 1:  $oldimg=imagecreatefromgif($file); break;
		case 2:  $oldimg=imagecreatefromjpeg($file); break;
		case 3:  $oldimg=imagecreatefrompng($file); break;
	}
	if($oldimg) {
		$newimg=imagecreatetruecolor(200, 200);
		if(imagecopyresampled($newimg, $oldimg, 0, 0, 0, 0, 200, 200, $owdt, $ohgt)){
			switch($otype) {
				case 1: imagegif($newimg,$file); break;
				case 2: imagejpeg($newimg,$file,90); break;
				case 3: imagepng($newimg,$file);  break;
			}
			imagedestroy($newimg);
		}else{
			//mislukt
		}
	}
}
?>