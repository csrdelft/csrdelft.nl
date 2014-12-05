<?php

require_once 'configuratie.include.php';

/**
 * suggesties.php    |     Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in suggesties voor typeahead
 *
 * request url: /tools/suggesties/{$zoekin}?q=zoeknaam&limit=20&timestamp=1336432238620
 */
if (!LoginModel::mag('P_LEDEN_READ') OR ! isset($_GET['q'])) {
	exit;
}

$limiet = 0;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

$result = array();
switch ($_GET['datatype']) {
	case 'document':
		require_once 'documenten/categorie.class.php';

		$categorie = 0;
		if (isset($_GET['categorie'])) {
			$categorie = (int) $_GET['categorie'];
		}

		$result = DocumentenCategorie::zoekDocumenten($_GET['q'], $categorie, $limiet);
		break;

	case 'boek':
		require_once 'bibliotheek/catalogus.class.php';

		$result = Catalogus::getAutocompleteSuggesties('biebboek');
		break;

	case 'groep':
		require_once 'groepen/groepen.class.php';

		$type = 0;
		if (isset($_GET['type'])) {
			$type = (int) $_GET['type'];
		}

		$result = Groepen::zoekGroepen($_GET['q'], $type, $limiet);
		break;
}

echo json_encode($result);
exit;
