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
	SimpleHTML::setMelding('Groeptype (' . mb_htmlentities($gtype) . ') bestaat niet', -1);
	redirect(CSR_ROOT . '/actueel/groepen/');
}

if (isset($_GET['maakOt']) AND $groepen->isAdmin()) {
	if ($groepen->maakGroepenOt()) {
		SimpleHTML::setMelding('De h.t. groepen in deze categorie zijn met succes o.t. gemaakt.', 1);
		redirect(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam());
	} else {
		SimpleHTML::setMelding('De h.t. groepen zijn niet allemaal met succes o.t. gemaakt.', -1);
		redirect(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam());
	}
}
if (isset($_GET['bewerken']) AND $groepen->isAdmin()) {
	$content->setAction('edit');
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['beschrijving'])) {
			$groepen->setBeschrijving($_POST['beschrijving']);
			if ($groepen->save()) {
				SimpleHTML::setMelding('Beschrijving van groepstype met succes opgeslagen.', 1);
				redirect(CSR_ROOT . '/actueel/groepen/' . $groepen->getNaam());
			} else {
				SimpleHTML::setMelding('Opslaan mislukt.', -1);
			}
		} else {
			SimpleHTML::setMelding('Opslaan mislukt. Geen inhoud gevonden.', -1);
		}
	}
}

$pagina = new CsrLayoutPage($content);
$pagina->addStylesheet('/layout/css/groepen');
$pagina->addScript('/layout/js/groepen');
$pagina->view();
