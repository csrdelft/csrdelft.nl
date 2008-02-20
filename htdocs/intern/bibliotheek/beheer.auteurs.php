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
		<script language="Javascript">
			// stopt alle auteurs in divAuteurs
			function selectAlleAuteurs() {
				ajaxRequestReturnTo('_beheer.auteurslijst.php?selectie=alle', 'divAuteurs');
			}
			
			// stopt alle auteurs zonder komma in divAuteurs
			function selectZonderKomma() {
				ajaxRequestReturnTo('_beheer.auteurslijst.php?selectie=zonderkomma', 'divAuteurs');
			}
			
			// zet de juiste auteur op edit
			function setToEdit(auteur_id) {
				ajaxRequestReturnTo('_beheer.auteurs.wijzig.form.php?auteur_id=' + auteur_id, 'divAuteur_' + auteur_id);
			}
			
			// wijzig de auteursnaam
			function wijzigAuteur(auteur_id) {
				ajaxRequestReturnTo('_beheer.auteurs.wijzig.php?auteur_id=' + auteur_id + '&auteur_naam=' + document.getElementById('auteurText_' + auteur_id).value, 'divAuteur_' + auteur_id);
			}
		</script>
		<div id="main">
			<h2>Auteurs</h2>
			<div id="divAuteurs">
			<? require_once("_beheer.auteurslijst.php"); ?>
			</div>
			
		</div>
<?
	printFooter();
?>