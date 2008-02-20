<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	//deze regel heb ik nu al echt vaak aangepast...
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
		echo "Fout: Dit is niet uw boek. U kunt teruggave daarom niet terugdraaien";
		exit;
	}
	
	$result = db_query("SELECT uitgeleend_uid, geleend_of_teruggegeven FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
	
	if (mysql_num_rows($result) == 0) {
		echo "Fout opgetreden: bevestigingverzoek onbekend";
		exit;
	} else {
		list($uitgeleend_uid, $geleend_of_teruggegeven) = mysql_fetch_row($result);
		if ($geleend_of_teruggegeven == "geleend") {
			# boek geleend terwijl dit terugdraaien van teruggave is. Onmogelijk.
			echo "Fout opgetreden: teruggave terugdraaien kan niet als het boek nog op bevestiging van lenen wacht (onmogelijk)";
			exit;
		} else {
			# in orde: teruggeven terugdraaien
			db_query("UPDATE biebexemplaar SET uitgeleend_uid = '$uitgeleend_uid' WHERE id = " . $e_id);
			db_query("DELETE FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
		}
	}
	
	echo "Teruggave van het boek succesvol teruggedraaid";
?>