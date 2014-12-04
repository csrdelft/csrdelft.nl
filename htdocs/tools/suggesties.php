<?php

require_once 'configuratie.include.php';

/**
 * suggesties.php    |     Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in naamsuggesties voor de jquery.autocomplete plugin
 * 
 * TODO: refactor naar typeahead
 *
 * request url: /tools/suggesties/{$zoekin}?q=zoeknaam&limit=20&timestamp=1336432238620
 * response: [{"data":["Jan Lid","x101"],"value":"Jan Lid","result":"Jan Lid"},{...}]
 */
if (!LoginModel::mag('P_LEDEN_READ')) {
	printmessage('Niet voldoende rechten');
	exit;
}

//datatype
$datatypes = array('document', 'boek', 'groep');
if (isset($_GET['datatype']) AND in_array($_GET['datatype'], $datatypes)) {
	$datatype = $_GET['datatype'];
} else {
	printmessage('Geen geldig datatype gegeven.');
	exit;
}

$result = array();
switch ($datatype) {
	case 'document':
		require_once 'documenten/categorie.class.php';

		$categorie = 0;
		if (isset($_GET['categorie'])) {
			$categorie = (int) $_GET['categorie'];
		}
		$limiet = (int) $_GET['limit'];

		if (isset($_GET['q'])) {
			$documenten = DocumentenCategorie::zoekDocumenten($_GET['q'], $categorie, $limiet);

			/** @var $document Document  */
			foreach ($documenten as $document) {
				$naam = $document->getNaam();
				$bestandsnaam = $document->getFileName();
				$id = $document->getID();

				$result[] = array('naam' => $naam, 'bestandsnaam' => $bestandsnaam, 'id' => $id);
			}
		}
		break;
	case 'boek':
		require_once 'bibliotheek/catalogus.class.php';

		if (isset($_GET['q'])) {
			$result = Catalogus::getAutocompleteSuggesties('biebboek');
		}
		break;
	case 'groep':
		require_once 'groepen/groepen.class.php';

		if (isset($_GET['q'])) {
			$type = 0;
			if (isset($_GET['type'])) {
				$type = (int) $_GET['type'];
			}
			$limiet = (int) $_GET['limit'];
			$result = Groepen::zoekGroepen($_GET['q'], $type, $limiet);
		}
		break;
}

echo json_encode($result);
exit;

function printmessage($error) {
	echo json_encode(array(array('error' => $error)));
}
