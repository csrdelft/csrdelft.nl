<?php
	require_once('inc.database.php');
	require_once('inc.common.php');

	/*
	 * begin toevoegen boek
	 */
		$geslaagd = false;
		
//		$titel 			= db_escape(getGET('titel'));
//		$auteur			= db_escape(getGET('auteur'));
//		$categorie		= (int) getGET('categorie');
//		$taal 			= db_escape(getGET('taal'));
//		$isbn 			= db_escape(getGET('isbn'));
//		$uitgavejaar 	= (int) getGET('uitgavejaar');
//		$paginas 		= (int) getGET('paginas');
//		$beschrijving 	= db_escape(getGET('beschrijving'));
//		$code 			= db_escape(getGET('code'));
//		$uitgeverij		= db_escape(getGET('uitgeverij'));
		
		$escapeForDB = true;
		include("_invoercontrole.php");
		
		// bij invoeren door bibliothecaris moet er ook een code worden ingevoerd
//		if ($code == "" && gebruikerIsAdmin()) $invoerOK = false;
		
		if ($invoerOK) {
			if ($uitgavejaar == "") $uitgavejaar = "NULL";
			if ($paginas == "") 	$paginas = "NULL";
			
			# check of de auteur al bestaat en voeg hem eventueel toe
			$result = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
			if (mysql_num_rows($result) == 0) {
				db_query("INSERT INTO biebauteur SET auteur = '$auteur'");
				$result = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
			} 
			list($a_id) = mysql_fetch_row($result);
			
			# check of het boek er al in staat en voeg hem eventueel toe
			$result = db_query("SELECT id FROM biebboek WHERE 
				titel = '$titel' AND
				auteur_id = $a_id AND
				taal = '$taal'
			");
			if (mysql_num_rows($result) == 0) {
				db_query("INSERT INTO biebboek SET 
					auteur_id = $a_id,
					categorie_id = $categorie,
					titel = '$titel',
					taal = '$taal',
					isbn = '$isbn', 
					paginas = $paginas,
					uitgavejaar = $uitgavejaar,
					uitgeverij = '$uitgeverij'
				");
				$result = db_query("SELECT id FROM biebboek WHERE 
					titel = '$titel' AND
					auteur_id = $a_id AND
					taal = '$taal'
				");
			} 
			list($boekID) = mysql_fetch_row($result);
			
			# voeg een exemplaar van het boek toe voor de ingelogde gebruiker
			$result = db_query("INSERT INTO biebexemplaar SET
				boek_id = $boekID,
				eigenaar_uid = '" . INGELOGD_UID . "',
				toegevoegd = " . time() . "
			");
			
			if ($beschrijving != '') {
				db_query("INSERT INTO biebbeschrijving SET
					boek_id = $boekID,
					schrijver_uid = '" . INGELOGD_UID . "',
					beschrijving = '$beschrijving',
					toegevoegd = " . time() . "
				");
			}
			
			if ($result) {
				$toegevoegdBoek = mb_htmlentities(stripslashes( $titel.' ('.$auteur.')' ));
				$geslaagd = true;
			}
		}
	/*
	 * eind toevoegen boek
	 */
	 
	if ($geslaagd) {
		echo "Boek succesvol toegevoegd: $toegevoegdBoek\n";
	} else {
		echo "Toevoegen mislukt\n";
	}
?>