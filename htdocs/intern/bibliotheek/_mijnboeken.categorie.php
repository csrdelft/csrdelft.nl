<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$p_id = (int) getGET("p_id");
?>
	<select name="categorie">
		<option value="0">selecteer een categorie
<?
		$result = db_query("SELECT id, categorie FROM biebcategorie WHERE p_id = " . $p_id . " ORDER BY categorie");
		
		while ( list($c_id, $c_naam) = mysql_fetch_row($result) ) {
?>
			<option value="<?=$c_id?>"><?=$c_naam?>
<?
		}
?>
	</select>