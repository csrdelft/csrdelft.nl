<?php

use CsrDelft\model\ProfielService;
use CsrDelft\model\security\LoginModel;

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
$toegestanezoekfilters = array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies');
if (isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)) {
	$zoekin = $_GET['zoekin'];
}
$query = '';
if (isset($_GET['q'])) {
	$query = $_GET['q'];
}
$limiet = 5;
if (isset($_GET['limit'])) {
	$limiet = (int) $_GET['limit'];
}

$profielen = ProfielService::instance()->zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $limiet);

$result = array();
foreach ($profielen as $profiel) {
	$tussenvoegsel = ($profiel->tussenvoegsel != '') ? $profiel->tussenvoegsel . ' ' : '';
	$fullname = $profiel->voornaam . ' ' . $tussenvoegsel . $profiel->achternaam;

	$result[] = array(
		'url'	 => '/profiel/' . $profiel->uid,
		'label'	 => $profiel->uid,
		'value'	 => $profiel->getNaam('volledig')
	);
}
/*
  if (empty($result)) {
  $result[] = array(
  'url' => '/ledenlijst?status=LEDEN|OUDLEDEN&q=' . urlencode($query),
  'label' => 'Zoeken in <span class="dikgedrukt">leden & oudleden</span>',
  'value' => htmlspecialchars($query)
  );
  }
 */
header('Content-Type: application/json');
echo json_encode($result);
exit;
