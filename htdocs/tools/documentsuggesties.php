<?php

require_once 'configuratie.include.php';
require_once 'documenten/categorie.class.php';

/**
 * documentsuggesties.php    |     Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in suggesties voor typeahead
 *
 * request url: /tools/documentsuggesties/{categorie}?q=zoeknaam&limit=20&timestamp=1336432238620
 */
if (!LoginModel::mag('P_DOCS_READ') OR ! isset($_GET['q'])) {
	exit;
} else {
	$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
}

$categorie = 0;
if (isset($_GET['cat'])) {
	$categorie = (int) $_GET['cat'];
}

$limiet = 5;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

$result = array();
foreach (DocumentenCategorie::zoekDocumenten($zoekterm, $categorie, $limiet) as $doc) {
	$result[] = array(
		'url'	 => '/communicatie/documenten/bekijken/' . $doc->getID() . '/' . $doc->getFileName(),
		'value'	 => $doc->getNaam() . '<span class="lichtgrijs"> - ' . $doc->getCategorie()->getNaam() . '</span>'
	);
}

header('Content-Type: application/json');
echo json_encode($result);
exit;
