<?php

/*
 * groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van de h.t. groepen per groepcategorie
 */

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_LOGGED_IN')) { // nieuwe layout altijd voor uitgelogde bezoekers
	redirect('/vereniging');
}

require_once 'model/GroepenOldModel.class.php';
require_once 'view/GroepenOldView.class.php';
require_once 'controller/GroepenController.class.php';


if (isset($_GET['gtype'])) {
	$gtype = $_GET['gtype'];
} else {
	$gtype = "Commissies";
}

try {
	$groepen = new GroepenOldModel($gtype);

	$content = new GroepenOldView($groepen);
} catch (Exception $e) {
	setMelding('Groeptype (' . htmlspecialchars($gtype) . ') bestaat niet', -1);
	redirect('/groepen/');
}

if (isset($_GET['maakOt']) AND $groepen->isAdmin()) {
	if ($groepen->maakGroepenOt()) {
		setMelding('De h.t. groepen in deze categorie zijn met succes o.t. gemaakt.', 1);
		redirect('/groepen/' . $groepen->getNaam());
	} else {
		setMelding('De h.t. groepen zijn niet allemaal met succes o.t. gemaakt.', -1);
		redirect('/groepen/' . $groepen->getNaam());
	}
}
if (isset($_GET['bewerken']) AND $groepen->isAdmin()) {
	$content->setAction('edit');
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['beschrijving'])) {
			$groepen->setBeschrijving($_POST['beschrijving']);
			if ($groepen->save()) {
				setMelding('Beschrijving van groepstype met succes opgeslagen.', 1);
				redirect('/groepen/' . $groepen->getNaam());
			} else {
				setMelding('Opslaan mislukt.', -1);
			}
		} else {
			setMelding('Opslaan mislukt. Geen inhoud gevonden.', -1);
		}
	}
}

$pagina = new CsrLayoutPage($content);
$pagina->addCompressedResources('groepen');
$pagina->view();
