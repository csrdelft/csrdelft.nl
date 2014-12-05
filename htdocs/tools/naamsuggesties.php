<?php

require_once 'configuratie.include.php';

/**
 * naamsuggesties.php	| 	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * voorziet in naamsuggesties voor de typeahead plugin
 * 
 * request url: /tools/naamsuggesties/{$zoekin}?q=zoeknaam&limit=20&timestamp=1336432238620
 * response: [{"data":["Jan Lid","x101"],"value":"Jan Lid","result":"Jan Lid"},{...}]
 */
if (!LoginModel::mag('P_LEDEN_READ')) {
	echo json_encode(array('key' => 0, 'value' => 'Niet voldoende rechten'));
	exit;
}

//welke subset van leden?
$zoekin = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID', 'S_ERELID');
$toegestanezoekfilters = array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies');
if (isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)) {
	$zoekin = $_GET['zoekin'];
}
$zoekterm = '';
if (isset($_GET['q'])) {
	$zoekterm = $_GET['q'];
}
$velden = array('uid', 'voornaam', 'tussenvoegsel', 'achternaam');
$limiet = null;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

$namen = Zoeker::zoekLeden($zoekterm, 'naam', 'alle', 'achternaam', $zoekin, $velden, $limiet);

$result = array();
foreach ($namen as $naam) {
	$tussenvoegsel = ($naam['tussenvoegsel'] != '') ? $naam['tussenvoegsel'] . ' ' : '';
	$fullname = $naam['voornaam'] . ' ' . $tussenvoegsel . $naam['achternaam'];

	$result[] = array('url' => '/communicatie/profiel/' . $naam['uid'], 'uid' => $naam['uid'], 'value' => $fullname);
}
echo json_encode($result);
exit;
