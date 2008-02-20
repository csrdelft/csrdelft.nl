<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$bev_id = (int) getGET('bev_id');
	
	# vraag geleend/teruggegeven en het exemplaar_id op
	$result = db_query("SELECT geleend_of_teruggegeven, exemplaar_id FROM biebbevestiging WHERE id = $bev_id");
	
	if ((mysql_num_rows($result) > 0) && ($row = mysql_fetch_row($result))) {
		
		# maak vast een melding klaar
		if ($row[0] == 'geleend') $melding = "Lenen van boek bevestigd";
		if ($row[0] == 'teruggegeven') $melding = "Teruggave van boek bevestigd";
		
		# check of ingelogde persoon wel eigenaar is van het boek
		if ( gebruikerMag(db_firstCell("SELECT eigenaar_uid FROM biebexemplaar WHERE id = " . $row[1])) ) {
			# bevestig door het bevestigingsverzoek te verwijderen
			db_query("DELETE FROM biebbevestiging WHERE id = $bev_id");
			
			# geef melding
			echo $melding;
		}
	} 
?>