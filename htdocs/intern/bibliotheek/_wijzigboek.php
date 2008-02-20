<?php
require_once('inc.database.php');
require_once('inc.common.php');

/*
   Opmerkingen over de logica van dit bestand:
   - controleren of de invoer correct is. Anders doet hij niets
   - kijken of er meer exemplaren van het boek zijn
	 ja  -> nieuw boek maken en boek_id van exemplaar op nieuw boek zetten
     nee -> kijken of het boek gewijzigd is in een bestaand boek
 			nee -> boek wijzigen
			ja ->  boek_id op bestaand boek zetten, 
			       [oude boek heeft geen exemplaren meer] -> boek weghalen
				   [oude boek heeft nog wel exemplaren]   -> boek laten staan
*/

/*
 * begin toevoegen boek
 */
$geslaagd = false;

$exemplaarID = (int) getGET('exemplaarID');

include("_invoercontrole.php");

// check of er wel een exemplaarID is meegegeven
if ($exemplaarID == "") {
	echo "Fout: exemplaarID niet meegegeven";
	exit;
}

// controleer of de ingelogde gebruiker de eigenaar van het boek is
$eigenaar_uid = db_firstCell("SELECT eigenaar_uid FROM biebexemplaar WHERE id=".$exemplaarID);
if ( !gebruikerMag($eigenaar_uid) ) {
	echo "Fout: u bent niet de eigenaar van het boek";
	exit;
}

if(isset($invoerOK) AND $invoerOK) {
	// default waarden voor in de DB:
	if ($uitgavejaar == "") $uitgavejaar = "NULL";
	if ($paginas == "") 	$paginas = "NULL";
	
	// check of er meerdere exemplaren zijn van het boek
	$boek_id = db_firstCell("SELECT boek_id FROM biebexemplaar WHERE id = " . $exemplaarID . ";");
	$aantalAndereExemplaren = db_firstCell("SELECT COUNT(*) FROM biebexemplaar WHERE boek_id = " . $boek_id . " AND eigenaar_uid <> '" . $eigenaar_uid . "'");
	
	if ($aantalAndereExemplaren == 0) { // als de ingelogde gebruiker als enige het boek heeft, mag hij veranderd worden
		/* (Er zijn NIET meer exemplaren van het boek) */
		// eerst kijken of hij nu gewijzigd is in een bestaand boek
		$idVanAuteurResult = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
		$auteur_gevonden = false;
		if (mysql_num_rows($idVanAuteurResult) == 1) {
			/* Er is één auteur gevonden die hetzelfde is als de ingevoerde. */
			$auteur_gevonden = true;
			
			$idVanAuteurArray = mysql_fetch_array($idVanAuteurResult);
			$idVanAuteur = $idVanAuteurArray['id'];
			
			$biebBoekIdResult = db_query("
				SELECT
					id
				FROM
					biebboek
				WHERE
					titel = '".$titel."' AND
					auteur_id = ".$idVanAuteur." AND
					taal = '".$taal."' AND
					categorie_id = '".$categorie."';
			"); //TODO: issets maken voor titel, taal en categorie? Escapen!?
		}
		
		if(isset($biebBoekIdResult) AND mysql_num_rows($biebBoekIdResult) == 1) { // boek is gewijzigd in een bestaande. Wijzig de boek_id en haal het oude boek eventueel weg
			/* Er is één boek in de DB waarvan de titel, auteur, taal en categorie gelijk is aan de ingevoerde gegevens. */
			list($nieuw_boek_id) = mysql_fetch_row($biebBoekIdResult);
			db_query("
				UPDATE
					biebexemplaar
				SET
					boek_id = ".$nieuw_boek_id."
				WHERE
					id = ".$exemplaarID.";
			");
			
			// het kan hier goed zo zijn dat het 'nieuwe boek' ook het oude boek is. Dus de gekregen waarden invoeren.
			db_query("
				UPDATE
					biebboek
				SET 
					auteur_id = $idVanAuteur,
					categorie_id = $categorie,
					titel = '$titel',
					taal = '$taal',
					isbn = '$isbn', 
					paginas = $paginas,
					uitgavejaar = $uitgavejaar,
					uitgeverij = '$uitgeverij'
				WHERE 
					titel = '$titel' AND
					auteur_id = $idVanAuteur AND
					taal = '$taal'
			");
			
			// checken of er nog exemplaren van het oude boek zijn
			$result = db_query("SELECT id FROM biebexemplaar WHERE boek_id = $boek_id");
			if (mysql_num_rows($result) == 0) {
			// oude boek kan weggehaald worden (geen exemplaren over)
				db_query("DELETE FROM biebboek WHERE id = $boek_id");
			}
		} else { // boek is niet gewijzigd in een bestaande. Wijzig dus het boek
			/* Er zijn nul of meerdere boeken in de DB waarvan de titel, auteur, taal en categorie gelijk is aan de ingevoerde gegevens. */
			
			// check of de auteur al bestaat en voeg hem eventueel toe
			if (mysql_num_rows($idVanAuteurResult) == 0) {
				$auteurInsert = db_query("INSERT INTO biebauteur SET auteur = '$auteur'");
				$insertedAuteurId = mysql_insert_id();
			}

			// Welke auteur-id moeten we hebben?!
			if(isset($insertedAuteurId)) {
				$updateAuteur = $insertedAuteurId;
			} else {
				$updateAuteur = $idVanAuteur;
			}

			// Vóór de update nog even de oude-auteur-id opvragen.
			if(isset($idVanAuteur)) {
				$oudeAuteurID = $idVanAuteur;
			} else {
				$oudeAuteurID = db_firstCell("SELECT auteur_id FROM biebboek WHERE id = ".$boek_id);
			}
			
			// De update van het biebboek.
			db_query("
				UPDATE biebboek SET 
					titel = '$titel',
					auteur_id = $updateAuteur,
					categorie_id = $categorie,
					taal = '$taal',
					isbn = '$isbn',
					paginas = $paginas,
					uitgavejaar = $uitgavejaar,
					uitgeverij = '$uitgeverij'
				WHERE
					id = $boek_id
			;");
			
			// Auteur verwijderen indien er geen boek meer is die er naar 'linkt'.
			verwijderAuteurIndienMogelijk($oudeAuteurID);
		}
		
		$geslaagd = true;
		
	} else { // als meer mensen het boek hebben, moet er een nieuw boek komen.
	
		/* (Er zijn meer exemplaren van het boek) */
		
		// check of de auteur al bestaat en voeg hem eventueel toe
		$result = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
		if (mysql_num_rows($result) == 0) {
			db_query("INSERT INTO biebauteur SET auteur = '$auteur'");
			$result = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
		} 
		list($a_id) = mysql_fetch_row($result);
		
		// check of het boek er al in staat en voeg hem eventueel toe
		$result = db_query("
			SELECT
				id
			FROM
				biebboek
			WHERE 
				titel = '$titel' AND
				auteur_id = $a_id AND
				taal = '$taal'
		;"); // ** En waar blijft de categorie?!
		if (mysql_num_rows($result) == 0) {
			db_query("
				INSERT INTO
					biebboek
				SET 
					auteur_id = $a_id,
					categorie_id = $categorie,
					titel = '$titel',
					taal = '$taal',
					isbn = '$isbn', 
					paginas = $paginas,
					uitgavejaar = $uitgavejaar,
					uitgeverij = '$uitgeverij'
			");
			echo 'insert id: '.mysql_insert_id();
			$boekID = mysql_insert_id();
			
			// update het exemplaar
			db_query("UPDATE biebexemplaar SET boek_id = $boekID WHERE id = $exemplaarID");
			
			// onthoud voor de administrator welk boek erbij is gemaakt, om eventuele dubbelen eruit te halen
			addToGewijzigd($exemplaarID, $boek_id, $a_id, $categorie, $titel, $taal, $isbn, $paginas, $uitgavejaar);
			
			if ($result) {
				$toegevoegdBoek = "$titel ($auteur)";
				$geslaagd = true;
			}
		} else {
			// boek staat er al in. Mag gewijzigd, mits het geen c.s.r.-biebboek is
			$code = db_firstCell("SELECT code FROM biebboek WHERE 
				titel = '$titel' AND
				auteur_id = $a_id AND
				taal = '$taal'
			");
			
			if ($code != "" && !gebruikerIsAdmin()) {
				// C.S.R.-boek; niet te wijzigen
				$melding = "Boek is van C.S.R.-bieb; kan niet gewijzigd worden behalve door de Bibliothecaris.";
			} else {
				// boek wijzigen
				$result = db_query("
					UPDATE biebboek SET 
						auteur_id = $a_id,
						categorie_id = $categorie,
						titel = '$titel',
						taal = '$taal',
						isbn = '$isbn', 
						paginas = $paginas,
						uitgavejaar = $uitgavejaar,
						uitgeverij = '$uitgeverij'
					WHERE 
						titel = '$titel' AND
						auteur_id = $a_id AND
						taal = '$taal'
				");
				
				if ($result) {
					$toegevoegdBoek = "$titel ($auteur)";
					$geslaagd = true;
				}
			}
		}
	}
}
/*
 * eind toevoegen boek
 */

//TODO: ok-melding weghalen?!
if ($geslaagd) {
	echo "ok";
} else {
	if ($melding == "") echo "Boek niet gewijzigd";
	else echo $melding;
}

// onthoud voor de administrator welk boek erbij is gemaakt, om eventuele dubbelen eruit te halen
function addToGewijzigd($exemplaarID, $boek_id, $a_id, $categorie, $titel, $taal, $isbn, $paginas, $uitgavejaar) {
	//er is niets te escapen hier???
	db_query("INSERT INTO biebadmingewijzigd SET 
		exemplaar_id = $exemplaarID,
		oud_boek_id = $boek_id,
		auteur_id = $a_id,
		categorie_id = $categorie,
		titel = '$titel',
		taal = '$taal',
		isbn = '$isbn', 
		paginas = $paginas,
		uitgavejaar = $uitgavejaar,
		uitgeverij = '$uitgeverij',
		tijdstip = '" . time() . "'
	");
}
?>