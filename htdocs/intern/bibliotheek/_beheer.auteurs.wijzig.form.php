<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$auteur_id = (int) getGET('auteur_id');
	
	$auteur_naam = db_firstCell("SELECT auteur FROM biebauteur WHERE id = " . $auteur_id);
?>
<input type="text" id="auteurText_<?=$auteur_id?>" value="<?=mb_htmlentities($auteur_naam)?>"> <a href="javascript:wijzigAuteur(<?=$auteur_id?>);">wijzig</a>