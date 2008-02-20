<?php
require_once('inc.database.php');
require_once('inc.common.php');

$e_id = (int) getGET("exemplaarID");
$nieuwe_lener_uid = 	getGET("lenerUID");
$nieuwe_lener_extern = 	getGET("extern");

if($nieuwe_lener_extern === null)
	$nieuwe_lener_extern = '';

# invoer nieuwe lener controleren
if (
	$nieuwe_lener_uid == '' || 
	($nieuwe_lener_uid == 'ext' && $nieuwe_lener_extern == '') || 
	($nieuwe_lener_uid != 'ext' && $nieuwe_lener_extern != '') ) 
{
	# foutieve informatie
	echo "Fout: informatie over persoon aan wie het boek wordt uitgeleend klopt niet<br>";
	exit;
}

$result = db_query("SELECT eigenaar_uid, uitgeleend_uid FROM biebexemplaar WHERE id = " . $e_id);

if (mysql_num_rows($result) == 0) {
	# exemplaar onbekend
	echo "Fout: exemplaar onbekend";
	exit;
}

list($eigenaar_uid, $uitgeleend_uid) = mysql_fetch_row($result);

if ( !gebruikerMag($eigenaar_uid) ) {
	# ingelogde gebruiker is geen eigenaar van het boek
	echo "Fout: u bent geen eigenaar van dit boek";
	exit;
}

if ($nieuwe_lener_uid == INGELOGD_UID) {
	# ingelogde gebruiker wil aan zichzelf uitlenen
	echo "Fout: u kunt geen boeken aan uzelf uitlenen";
	exit;
}

if ($uitgeleend_uid != '') {
	# boek is al uitgeleend
	echo "Fout: boek is al uitgeleend";
	exit;
}

# check de bevestigingen
$result = db_query("SELECT geleend_of_teruggegeven FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
if (mysql_num_rows($result) > 0) {
	list($geleend_of_teruggegeven) = mysql_fetch_row($result);
	if ($geleend_of_teruggegeven == "teruggegeven") {
		# teruggave van het boek nog niet bevestigd
		echo "Fout: u hebt nog niet bevestigd dat iemand anders u het boek heeft teruggegeven";
	} else {
		# lenen van het boek door iemand anders nog niet bevestigd
		echo "Fout: iemand heeft het boek al geleend, maar dat hebt u nog niet bevestigd";
	}
	
	exit;
}

# in orde: boek op uitgeleend zetten
db_query("UPDATE biebexemplaar SET uitgeleend_uid = '$nieuwe_lener_uid', extern = '$nieuwe_lener_extern' WHERE id = " . $e_id);
echo "ok";
?>