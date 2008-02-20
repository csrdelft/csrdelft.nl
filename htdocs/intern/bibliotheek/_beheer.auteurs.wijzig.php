<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
 	$auteur_id = (int) getGET("auteur_id");
	$auteur_naam = getGET("auteur_naam");
	
	$result = db_query("SELECT id FROM biebauteur WHERE auteur = '$auteur_naam'");
	
	if (mysql_num_rows($result) > 0) {
		# auteur bestaat al. id uit result vissen en die vervangen bij alle boeken
		list($new_auteur_id) = mysql_fetch_row($result);
		db_query("UPDATE biebboek SET auteur_id = $new_auteur_id WHERE auteur_id = $auteur_id");
		
		# oude auteur verwijderen
		db_query("DELETE FROM biebauteur WHERE id = $auteur_id");
		
		$auteur_id = $new_auteur_id;
	} else {
		db_query("UPDATE biebauteur SET auteur = '$auteur_naam' WHERE id = $auteur_id");
	}
?>
Gewijzigd in <a href="javascript:setToEdit(<?=$auteur_id?>);"><?=mb_htmlentities(stripslashes($auteur_naam))?></a>