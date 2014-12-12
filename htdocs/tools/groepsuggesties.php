<?php

require_once 'configuratie.include.php';
require_once 'groepen/groepen.class.php';

/**
 * groepsuggesties.php    |     Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in suggesties voor typeahead
 *
 * request url: /tools/groepsuggesties/{$type}?q=zoeknaam&limit=20&timestamp=1336432238620
 */
if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ') OR ! isset($_GET['q'])) {
	exit;
} else {
	$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
}

$gtype = 0;
if (isset($_GET['gtype'])) {
	$gtype = (int) $_GET['gtype'];
}

$limiet = 5;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

header('Content-Type: application/json');
echo json_encode(GroepenOldModel::zoekGroepen($zoekterm, $gtype, $limiet));
exit;
