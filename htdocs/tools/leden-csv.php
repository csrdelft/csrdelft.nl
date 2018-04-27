<?php

use CsrDelft\MijnSqli;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/leden-csv.php
# -------------------------------------------------------------------
# Geeft de leden, gastleden, kringels en novieten terug in een csv-bestand.

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	redirect(CSR_ROOT);
}

header('Content-Type: text/csv');
$sLedenQuery = "
	SELECT 
		uid, voornaam, achternaam, tussenvoegsel, CONCAT(moot, '.', kring) AS kring,
		adres, postcode, woonplaats, telefoon, mobiel, email,
		o_adres, o_postcode, o_woonplaats, o_telefoon, 
		gebdatum
	FROM
		lid
	WHERE
		status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'
	ORDER BY achternaam, voornaam;";

$db = MijnSqli::instance();
$rLeden = $db->query($sLedenQuery);

while ($aLid = $db->next($rLeden)) {
	foreach ($aLid as $key => $veld) {
		$veld = trim($veld);
		switch ($key) {
			case 'mobiel':
			case 'telefoon':
			case 'o_telefoon':
				if ($veld != '') {
					echo internationalizePhonenumber($veld);
				}
				break;
			default:
				echo $veld;
		}
		echo ';';
	}
	echo "\n";
}
