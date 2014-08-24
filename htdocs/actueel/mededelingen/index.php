<?php

require_once 'configuratie.include.php';
require_once 'mededelingen/mededeling.class.php';
require_once 'mededelingen/mededelingcontent.class.php';
require_once 'mededelingen/mededelingencontent.class.php';

$mededelingId = 0;
if (isset($_GET['mededelingId'])) {
	$mededelingId = (int) $_GET['mededelingId'];
}

$actie = 'default';
if (isset($_GET['actie'])) {
	$actie = $_GET['actie'];
}

if (isset($_GET['pagina'])) {
	$pagina = (int) $_GET['pagina'];
}

$prullenbak = false;
if (isset($_REQUEST['prullenbak']) AND $_REQUEST['prullenbak'] == '1' AND Mededeling::isModerator()) {
	$prullenbak = true;
}

switch ($actie) {
	case 'verwijderen':
		if (!Mededeling::magToevoegen()) {
			redirect(CSR_ROOT);
		}
		if ($mededelingId > 0) {
			$mededeling = new Mededeling($mededelingId);
			if (Mededeling::isModerator() OR $mededeling->getUid() == LoginModel::getUid()) {
				$verwijderd = $mededeling->delete();
				if ($verwijderd === false) {
					SimpleHTML::setMelding('Het verwijderen is mislukt.', -1);
				} else {
					SimpleHTML::setMelding('De mededeling is succesvol verwijderd.', 1);
				}
			} else { // Dit lid mag deze mededeling helemaal niet verwijderen!
				redirect(CSR_ROOT);
			}
		}
		$content = new MededelingenContent(0, $prullenbak);
		// De eerste pagina laden.
		$content->setPaginaNummer(1);
		break;

	case 'bewerken':
		if (!Mededeling::magToevoegen()) {
			redirect(CSR_ROOT);
		}

		if (isset($_POST['titel'], $_POST['tekst'], $_POST['categorie'])) {
			// The user is editing an existing Mededeling or tried adding a new one.
			// Get properties from $_POST.
			$mededelingProperties = array();
			$mededelingProperties['id'] = $mededelingId;
			$mededelingProperties['titel'] = $_POST['titel'];
			$mededelingProperties['tekst'] = $_POST['tekst'];
			$mededelingProperties['datum'] = getDateTime();
			if (isset($_POST['vervaltijd'])) {
				$mededelingProperties['vervaltijd'] = $_POST['vervaltijd'];
			}
			$mededelingProperties['uid'] = LoginModel::getUid();
			if (isset($_POST['prioriteit'])) {
				$mededelingProperties['prioriteit'] = (int) $_POST['prioriteit'];
			}
			$mededelingProperties['doelgroep'] = $_POST['doelgroep'];
			if (!Mededeling::isModerator()) {
				$mededelingProperties['zichtbaarheid'] = 'wacht_goedkeuring';
			} else {
				$mededelingProperties['zichtbaarheid'] = isset($_POST['verborgen']) ? 'onzichtbaar' : 'zichtbaar';
			}
			$mededelingProperties['categorie'] = (int) $_POST['categorie'];

			$allOK = true; // This variable is set to false if there is an error.
			// Special treatment for the picture.
			$mededelingProperties['plaatje'] = '';
			if (isset($_FILES['plaatje']) AND $_FILES['plaatje']['error'] == UPLOAD_ERR_OK) { // If uploading succeedded.
				$info = getimagesize($_FILES['plaatje']['tmp_name']);
				if ($info[0] != 0 AND $info[1] != 0) {
					if (($info[0] / $info[1]) == 1) { // If the ratio is fine (1:1).
						$pictureFilename = $_FILES['plaatje']['name'];
						$pictureFullPath = PICS_PATH . 'nieuws/' . $pictureFilename; // TODO: change nieuws to mededelingen
						if (move_uploaded_file($_FILES['plaatje']['tmp_name'], $pictureFullPath) !== false) {
							$mededelingProperties['plaatje'] = $pictureFilename;
							if ($info[0] != 200) { // Too big, resize it.
								resize_plaatje($pictureFullPath);
							}
							chmod($pictureFullPath, 0644);
						} else {
							SimpleHTML::setMelding('Plaatje verplaatsen is mislukt.', -1);
							$allOK = false;
						}
					} else {
						SimpleHTML::setMelding('Plaatje is niet in de juiste verhouding.', -1);
						$allOK = false;
					}
				} else {
					SimpleHTML::setMelding('Het is niet gelukt om de resolutie van het plaatje te bepalen.', -1);
					$allOK = false;
				}
			}

			// Check if all values appear to be OK.
			$tijdelijkeMededeling = $mededelingId > 0 ? new Mededeling($mededelingId) : null;
			if (strlen($mededelingProperties['titel']) < 2) {
				SimpleHTML::setMelding('Het veld <b>Titel</b> moet minstens 2 tekens bevatten.', -1);
				$allOK = false;
			}
			if (strlen($mededelingProperties['tekst']) < 5) {
				SimpleHTML::setMelding('Het veld <b>Tekst</b> moet minstens 5 tekens bevatten.', -1);
				$allOK = false;
			}

			// Check vervaltijd.
			if (!isset($_POST['vervaltijdAan'], $_POST['vervaltijd'])) {
				// Indien de gebruiker geen einddatum wil, reset deze!
				$mededelingProperties['vervaltijd'] = null;
			} else {
				$vervaltijd = strtotime($mededelingProperties['vervaltijd']);
				if ($vervaltijd === false OR ! isGeldigeDatum($mededelingProperties['vervaltijd'])) {
					SimpleHTML::setMelding('Vervaltijd is ongeldig.', -1);
					$allOK = false;
				} else {
					$datum = strtotime($mededelingProperties['datum']);
					if ($vervaltijd <= $datum) {
						SimpleHTML::setMelding('Vervaltijd moet groter zijn dan de huidige tijd.', -1);
						$allOK = false;
					}
				}
			}

			// Check prioriteit.
			$prioriteitIsOngeldig = true;
			if (isset($mededelingProperties['prioriteit'])) {
				$prioriteitIsOngeldig = (array_search($mededelingProperties['prioriteit'], array_keys(Mededeling::getPrioriteiten())) === false);
			}
			// Indien de gebruiker geen moderator is OF de prioriteit ongeldig is.
			if (!Mededeling::isModerator() OR $prioriteitIsOngeldig) {
				if ($tijdelijkeMededeling !== null) { // We bewerken, dus huidige prioriteit behouden.
					$mededelingProperties['prioriteit'] = $tijdelijkeMededeling->getPrioriteit();
				} else { // We voegen toe, dus default prioriteit gebruiken.
					$mededelingProperties['prioriteit'] = Mededeling::defaultPrioriteit;
				}
			}

			// Check doelgroep.
			if (array_search($mededelingProperties['doelgroep'], Mededeling::getDoelgroepen()) === false) {
				SimpleHTML::setMelding('De doelgroep is ongeldig.', -1);
				$allOK = false;
			}

			// Check categorie.
			$categorieValid = false;
			foreach (MededelingCategorie::getCategorieen() as $categorie) {
				$hetIsDeze = ($mededelingProperties['categorie'] == $categorie->getId());
				$categorieOnveranderd = ($tijdelijkeMededeling !== null AND $tijdelijkeMededeling->getCategorieId() == $mededelingProperties['categorie']);
				if ($hetIsDeze AND ( $categorie->magUitbreiden() OR $categorieOnveranderd)) {
					$categorieValid = true;
				}
			}
			if (!$categorieValid) {
				$mededelingProperties['categorie'] = null;
				SimpleHTML::setMelding('De categorie is ongeldig.', -1);
				$allOK = false;
			}
			// Check picture.
			if (empty($mededelingProperties['plaatje']) AND $mededelingId == 0) { // If there's no new picture, while there should be.
				$errorNumber = $_FILES['plaatje']['error'];
				if ($errorNumber == UPLOAD_ERR_NO_FILE) { // If there was no file being uploaded at all. 
					SimpleHTML::setMelding('Het toevoegen van een plaatje is verplicht.', -1);
					$allOK = false;
				} elseif ($errorNumber != UPLOAD_ERR_OK) {
					// Uploading the picture failed.
					$allOK = false;
				}
				// The last possibility, $errorNumber==UPLOAD_ERR_OK is being issued above, where the picture
				// is being moved.
			}

			$mededeling = new Mededeling($mededelingProperties);
			if ($allOK) {
				// Save the mededeling to the database. (Either via UPDATE or INSERT).
				$realId = $mededeling->save();
				if ($realId == -1) { // If something went wrong, just go to the main page.
					$realId = '';
				}
				//TODO: Melding weergeven dat er iets toegevoegd is (?)
				$nieuweLocatie = MededelingenContent::mededelingenRoot;
				if ($prullenbak) {
					$nieuweLocatie .= '/prullenbak';
				}
				$nieuweLocatie .= '/' . $realId;
				redirect($nieuweLocatie);
			}
		} else { // User is going to edit an existing Mededeling or fill in an empty form.
			$mededeling = new Mededeling($mededelingId);
		}

		// Controleren of de gebruiker deze mededeling wel mag bewerken.
		if ($mededelingId > 0 AND ! $mededeling->magBewerken()) { // Moet dit niet eerder gebeuren?
			redirect(CSR_ROOT); // Misschien melding weergeven en terug gaan naar de mededelingenpagina? 
		}
		$content = new MededelingContent($mededeling, $prullenbak);
		break;

	default:
		$content = new MededelingenContent($mededelingId, $prullenbak);
		if (isset($pagina)) { // Als de gebruiker een pagina opvraagt.
			$content->setPaginaNummer($pagina);
		} elseif ($mededelingId == 0) { // Als de gebruiker GEEN pagina opvraagt en ook geen mededeling.
			$content->setPaginaNummer(1);
		}
		break;
}

$pagina = new CsrLayoutPage($content);
$pagina->addStylesheet('/layout/css/mededelingen');
$pagina->view();

function resize_plaatje($file) {
	list($owdt, $ohgt, $otype) = @getimagesize($file);
	switch ($otype) {
		case 1: $oldimg = imagecreatefromgif($file);
			break;
		case 2: $oldimg = imagecreatefromjpeg($file);
			break;
		case 3: $oldimg = imagecreatefrompng($file);
			break;
	}
	if ($oldimg) {
		$newimg = imagecreatetruecolor(200, 200);
		if (imagecopyresampled($newimg, $oldimg, 0, 0, 0, 0, 200, 200, $owdt, $ohgt)) {
			switch ($otype) {
				case 1: imagegif($newimg, $file);
					break;
				case 2: imagejpeg($newimg, $file, 90);
					break;
				case 3: imagepng($newimg, $file);
					break;
			}
			imagedestroy($newimg);
		} else {
			//mislukt
		}
	}
}
