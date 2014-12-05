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
if (!LoginModel::mag('P_LEDEN_READ') OR ! isset($_GET['q'])) {
	exit;
} else {
	$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
}

$type = 0;
if (isset($_GET['type'])) {
	$type = (int) $_GET['type'];
}

$limiet = 0;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

echo json_encode(Groepen::zoekGroepen($zoekterm, $type, $limiet));
exit;
