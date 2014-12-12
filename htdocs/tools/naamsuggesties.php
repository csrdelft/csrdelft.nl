<?php

require_once 'configuratie.include.php';

/**
 * naamsuggesties.php	| 	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in naamsuggesties voor de typeahead plugin
 * 
 * request url: /tools/naamsuggesties/{$zoekin}?q=zoeknaam&limit=20&timestamp=1336432238620
 */
if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	echo 'Niet voldoende rechten';
	exit;
}

//welke subset van leden?
$zoekin = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID', 'S_ERELID');
$toegestanezoekfilters = array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies');
if (isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)) {
	$zoekin = $_GET['zoekin'];
}
$query = '';
if (isset($_GET['q'])) {
	$query = $_GET['q'];
}
$velden = array('uid', 'voornaam', 'tussenvoegsel', 'achternaam');
$limiet = 5;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

$namen = Zoeker::zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $velden, $limiet);

$result = array();
foreach ($namen as $naam) {
	$tussenvoegsel = ($naam['tussenvoegsel'] != '') ? $naam['tussenvoegsel'] . ' ' : '';
	$fullname = $naam['voornaam'] . ' ' . $tussenvoegsel . $naam['achternaam'];

	$result[] = array(
		'url'	 => '/profiel/' . $naam['uid'],
		'value'	 => $fullname
	);
}
/*
if (empty($result)) {
	$result[] = array(
		'url'	 => '/ledenlijst?status=LEDEN|OUDLEDEN&q=' . urlencode($query),
		'value'	 => htmlspecialchars($query) . '<span class="lichtgrijs"> - Zoeken in <span class="dikgedrukt">leden & oudleden</span></span>'
	);
}
*/
header('Content-Type: application/json');
echo json_encode($result);
exit;
