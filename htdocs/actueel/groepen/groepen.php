<?php

/*
 * groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van de h.t. groepen per groepcategorie
 */

require_once 'configuratie.include.php';

require_once 'groepen/groep.class.php';
require_once 'groepen/groepcontent.class.php';
require_once 'groepen/groepcontroller.class.php';


if (isset($_GET['gtype'])) {
	$gtype = $_GET['gtype'];
} else {
	$gtype = "Commissies";
}

try {
	$groepen = new Groepen($gtype);

	$content = new Groepencontent($groepen);
} catch (Exception $e) {
	invokeRefresh(CSR_ROOT . '/actueel/groepen/', 'Groeptype (' . mb_htmlentities($gtype) . ') bestaat niet');
}

if (isset($_GET['maakOt']) AND $groepen->isAdmin()) {
	if ($groepen->maakGroepenOt()) {
		invokeRefresh(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam(), 'De h.t. groepen in deze categorie zijn met succes o.t. gemaakt.', 1);
	} else {
		invokeRefresh(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam(), 'De h.t. groepen zijn niet allemaal met succes o.t. gemaakt.');
	}
}
if (isset($_GET['bewerken']) AND $groepen->isAdmin()) {
	$content->setAction('edit');
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['beschrijving'])) {
			$groepen->setBeschrijving($_POST['beschrijving']);
			if ($groepen->save()) {
				invokeRefresh(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam(), 'Beschrijving van groepstype met succes opgeslagen.', 1);
			} else {
				setMelding('Opslaan mislukt.', -1);
			}
		} else {
			setMelding('Opslaan mislukt. Geen inhoud gevonden.', -1);
		}
	}
}

$pagina = new CsrLayoutPage($content);
$pagina->addStylesheet('/layout/css/groepen.css');
$pagina->addScript('/layout/js/groepen.js');
$pagina->view();
