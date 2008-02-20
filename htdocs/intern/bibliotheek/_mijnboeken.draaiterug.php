<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$bev_id = (int) getGET('bev_id');
	
	# check of het wel om een geleend boek gaat
	$result = db_query("SELECT geleend_of_teruggegeven, exemplaar_id FROM biebbevestiging WHERE id = $bev_id");
	
	if ((mysql_num_rows($result) > 0) && ($row = mysql_fetch_row($result))) {
		
		# check of ingelogde persoon wel eigenaar is van het boek
		if ( gebruikerMag((db_firstCell("SELECT eigenaar_uid FROM biebexemplaar WHERE id = " . $row[1]))) ) {
			
			if ($row[0] == 'geleend') {
				# lenen van boek ongedaan maken
				db_query("UPDATE biebexemplaar SET uitgeleend_uid = '' WHERE id = " . $row[1]);
				db_query("DELETE FROM biebbevestiging WHERE id = " . $bev_id);
				$melding = "Lenen van boek ongedaan gemaakt";
			}
			
			if ($row[0] == 'teruggegeven') {
				# teruggeven van boek ongedaan maken
				$uitgeleend_uid = db_firstCell("SELECT uitgeleend_uid FROM biebbevestiging WHERE id = " . $bev_id);
				db_query("UPDATE biebexemplaar SET uitgeleend_uid = '$uitgeleend_uid' WHERE id = " . $row[1]);
				db_query("DELETE FROM biebbevestiging WHERE id = " . $bev_id);
				$melding = "Teruggave van boek ongedaan gemaakt";
			}
		}
			
		# geef melding
		echo $melding;
	} 
?>