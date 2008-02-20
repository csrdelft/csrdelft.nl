<?php
require_once('inc.database.php');
require_once('inc.common.php');

$exemplaarID = (int) getGET("exemplaarID");

$result = db_query("SELECT eigenaar_uid, uitgeleend_uid FROM biebexemplaar WHERE id = " . $exemplaarID);

if (mysql_num_rows($result) == 0) {
	// exemplaar onbekend
	echo "Fout: exemplaar onbekend";
	exit;
}

list($eigenaar_uid, $uitgeleend_uid) = mysql_fetch_row($result);

if ( !gebruikerMag($eigenaar_uid) ) {
	// ingelogde gebruiker is geen eigenaar van het boek
	echo "Fout: u bent geen eigenaar van dit boek";
	exit;
}

if ($uitgeleend_uid != '') {
	// boek is uitgeleend
	echo "Fout: boek is uitgeleend. Een uitgeleend boek kan niet worden verwijderd.";
	exit;
}

$result = db_query("SELECT id FROM biebbevestiging WHERE exemplaar_id = " . $exemplaarID);
if (mysql_num_rows($result) > 0) {
	// er staan nog bevestigingen
	echo "Fout: terugbrengen van het boek is nog niet bevestigd. Een boek met openstaande bevestigingsverzoeken kan niet worden verwijderd";
	exit;
}

// Het BoekID opvragen van dit exemplaar vóórdat we 'm verwijderen.
$boekID = db_firstCell("SELECT boek_id FROM biebexemplaar WHERE id = ".$exemplaarID);

// Boek(exemplaar) verwijderen.
db_query("DELETE FROM biebexemplaar WHERE id = " . $exemplaarID);

// Aantal (overige) exemplaren opvragen van het boek.
$aantalExemplaren = db_firstCell("SELECT COUNT(*) FROM biebexemplaar WHERE boek_id = ".$boekID);
if( $aantalExemplaren == 0 ) {		// Geen exemplaren meer, dus boek kan weg!
	// Eerst nog even de auteur opvragen, vóórdat het boek verwijderd wordt.
	$auteurID = db_firstCell("SELECT auteur_id FROM biebboek WHERE id = ".$boekID);
	
	// Beschrijving(en) verwijderen
	db_query("DELETE FROM biebbeschrijving WHERE boek_id = ".$boekID);
	
	// Boek verwijderen
	db_query("DELETE FROM biebboek WHERE id = ".$boekID);
	
	// Auteur verwijderen indien er geen boeken meer zijn met deze auteur.
	verwijderAuteurIndienMogelijk($auteurID);
}

echo "Boek succesvol verwijderd";
?>