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
	
	if ( !gebruikerMag($eigenaar_uid) ) {
		# ingelogde gebruiker is geen eigenaar van het boek
		echo "Fout: Dit is niet uw boek. U kunt teruggave daarom niet bevestigen";
		exit;
	}
	
	$result = db_query("SELECT geleend_of_teruggegeven FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
	
	if (mysql_num_rows($result) == 0) {
		echo "Fout: bevestigingverzoek onbekend";
		exit;
	} else {
		list($geleend_of_teruggegeven) = mysql_fetch_row($result);
		if ($geleend_of_teruggegeven == "lenen") {
			# boek teruggegeven terwijl dit bevestigen van lenen is. Onmogelijk.
			echo "Fout opgetreden: lenen bevestigen kan niet als het boek nog op bevestiging van teruggave wacht (onmogelijk)";
			exit;
		} else {
			# in orde: bevestingverzoek weghalen
			db_query("DELETE FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
		}
	}
	
	echo "Lenen van het boek succesvol bevestigd";
?>