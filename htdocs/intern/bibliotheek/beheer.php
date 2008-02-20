<?php
	require_once('inc.database.php');
	require_once('inc.html.php');
	require_once('inc.common.php');
	
	# controleer op rechten
	if (!gebruikerIsAdmin()) {
		echo "U hebt geen beheerrechten";
		exit;
	}
	
	printHeader();
?>
		<div id="main">
			<h2>Beheer</h2>
			<p>Welkom op de beheerpagina van de C.S.R.-gedistribueerde bibliotheek.</p>
			<p><a href="beheer.auteurs.php">Auteurs beheren</a></p>
		</div>
<?
	printFooter();
?>