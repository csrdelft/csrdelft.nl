<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$selectie = getGET("selectie");
	
	if ($selectie == "alle") { 
		?><a href="javascript:selectZonderKomma();">Auteurs zonder komma</a><? 
	} else { 
		?><a href="javascript:selectAlleAuteurs();">Alle auteurs</a><? 
	}
	
	if ($selectie != "alle") $WHERE_CLAUSE = "WHERE auteur NOT LIKE '%,%'";
	$result = db_query("SELECT id, auteur FROM biebauteur $WHERE_CLAUSE ORDER BY auteur");
	
	while (list($auteur_id, $auteur_naam) = mysql_fetch_row($result)) {
?>
		<div id="divAuteur_<?=$auteur_id?>"><a href="javascript:setToEdit(<?=$auteur_id?>);"><?=$auteur_naam?></a></div>
<?
	}
?>