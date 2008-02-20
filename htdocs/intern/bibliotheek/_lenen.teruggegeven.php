<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$e_id = (int) getGET("exemplaarID");
	
	$result = db_query("SELECT eigenaar_uid, uitgeleend_uid FROM biebexemplaar WHERE id = " . $e_id);
	
	if (mysql_num_rows($result) == 0) {
		# exemplaar onbekend
		echo "Fout: exemplaar onbekend";
		exit;
	}
	
	list($eigenaar_uid, $uitgeleend_uid) = mysql_fetch_row($result);
	
	if ( !gebruikerMag($uitgeleend_uid) ) {
		# ingelogde gebruiker heeft het boek niet geleend
		echo "Fout: u hebt het boek niet geleend; u kunt het dus ook niet teruggeven.";
		exit;
	}
	
	$result = db_query("SELECT geleend_of_teruggegeven FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
	
	if (mysql_num_rows($result) > 0) {
		list($geleend_of_teruggegeven) = mysql_fetch_row($result);
		if ($geleend_of_teruggegeven == "teruggegeven") {
			# Opmerking: Dit is onmogelijk. Iemand anders zou hem teruggebracht hebben, terwijl het
			#            niet mogelijk is een boek te lenen indien teruggave door iemand anders nog 
			#            niet bevestigd is
			echo "Fout opgetreden: boek al door iemand anders teruggebracht (onmogelijk)";
			exit;
		} else {
			# - lenen van het boek nog niet bevestigd
			# - bevestigingverzoek eruit halen; boek is toch al terug. Ook geen bevestiging van 
			#   teruggeven erin. Tevens uitgeleendheid weghalen.
			db_query("UPDATE biebexemplaar SET uitgeleend_uid = '', extern = '' WHERE id = " . $e_id);
			db_query("DELETE FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
		}
	} else {
		# in orde. Boek op 'niet uitgeleend' zetten en bevestigingverzoek plaatsen
		db_query("UPDATE biebexemplaar SET uitgeleend_uid = '', extern = '' WHERE id = " . $e_id);
		db_query("INSERT INTO biebbevestiging SET 
				exemplaar_id = $e_id, 
				uitgeleend_uid = '" . INGELOGD_UID . "', 
				geleend_of_teruggegeven = 'teruggegeven',
				timestamp = " . time() . "
		");
	}
	
	echo "Teruggave van het boek succesvol gemeld";
?>