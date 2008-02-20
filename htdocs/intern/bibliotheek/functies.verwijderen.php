<?php

/*
 * 	functies.verwijderen.php
 *	
 *	Hier verzamelen we algemene functies die te maken hebben met
 *	het verwijderen van iets uit de DB.
 *
 */

function verwijderAuteurIndienMogelijk($auteurid) {
	// Kijken of de auteur verwijderd mag worden.
	$aantalBoeken = db_firstCell("
		SELECT
			COUNT(*)
		FROM
			biebboek
		WHERE
			auteur_id = ".$auteurid
	);
	if($aantalBoeken == 0) {	// Ja, er zijn geen boeken meer met de ingevoerde
								// auteur, dus de auteur mag weg!
		db_query("DELETE FROM biebauteur WHERE id = ".$auteurid);
	}
}
?>
