<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/leden-csv.php
# -------------------------------------------------------------------
# Geeft de leden, gastleden, kringels en novieten terug in een csv-bestand.


require_once('include.config.php');


if(!$lid->hasPermission('P_LEDEN_READ')){ header('location: '.CSR_ROOT); }


header('content-type: text/csv');
$sLedenQuery="
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

$rLeden=$db->query($sLedenQuery);

while($aLid=$db->next($rLeden)){
	echo implode($aLid, ';')."\n";
}

?>
