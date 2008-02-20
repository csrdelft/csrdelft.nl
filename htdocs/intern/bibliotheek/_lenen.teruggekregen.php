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
		echo "Fout: u bent geen eigenaar van dit boek";
		exit;
	}
	
	if ($uitgeleend_uid == '') {
		# boek is niet uitgeleend
		echo "Fout: boek is niet uitgeleend";
		exit;
	}
	
	# in orde: boek op 'niet uitgeleend' zetten en eventuele bevestiging weghalen
	db_query("UPDATE biebexemplaar SET uitgeleend_uid = '', extern = '' WHERE id = " . $e_id);
	db_query("DELETE FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
	echo "Boek succesvol ingesteld op 'teruggekregen'";
?>