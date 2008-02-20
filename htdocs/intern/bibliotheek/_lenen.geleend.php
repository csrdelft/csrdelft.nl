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
	
	if ( gebruikerIs($eigenaar_uid) ) {
		# probeert eigen boek te lenen
		echo "Fout: uw eigen boek lenen is niet mogelijk. id ";
		exit;
	}
	
	if ($uitgeleend_uid != '') {
		# boek is al uitgeleend
		echo "Fout: het boek is volgens de database reeds uitgeleend";
		exit;
	}
	
	$result = db_query("SELECT geleend_of_teruggegeven FROM biebbevestiging WHERE exemplaar_id = " . $e_id);
	
	if (mysql_num_rows($result) > 0) {
		list($geleend_of_teruggegeven) = mysql_fetch_row($result);
		if ($geleend_of_teruggegeven == "teruggegeven") {
			# - het is niet mogelijk om een boek te lenen als de teruggave van het boek door iemand anders
			#   nog niet is bevestigd. Daar wordt de database te ingewikkeld van
			echo "Fout: teruggave van het boek door iemand anders is door de eigenaar nog niet bevestigd";
			exit;
		} else {
			# - lenen van het boek door iemand anders nog niet bevestigd. Onmogelijk, dan zou het niet 
			#   mogelijk moeten zijn om het boek te lenen
			echo "Fout opgetreden: het boek is door iemand anders geleend, wat nog niet bevestigd is (dit is onmogelijk)";
			exit;
		}
	}
	
	# in orde: boek op geleend zetten en een bevestigingverzoek plaatsen
	db_query("UPDATE biebexemplaar SET uitgeleend_uid = '" . INGELOGD_UID . "', extern = '' WHERE id = " . $e_id);
	db_query("INSERT INTO biebbevestiging SET 
			exemplaar_id = $e_id, 
			uitgeleend_uid = '" . INGELOGD_UID . "', 
			geleend_of_teruggegeven = 'geleend',
			timestamp = " . time() . "
	");
	echo "Boek succesvol geleend";
?>