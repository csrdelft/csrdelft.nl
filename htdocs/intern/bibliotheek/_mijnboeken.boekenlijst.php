<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
?>
	<h2>Mijn boeken</h2>
	<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">
<?
	# boeken ophalen
	$result = db_query("
		SELECT 
			e.id, e.eigenaar_uid, e.uitgeleend_uid,
			b.id, b.titel, b.taal, b.isbn, b.uitgavejaar,
			a.auteur,
			c.categorie
		FROM 
			biebexemplaar e, biebboek b, biebauteur a, biebcategorie c
		WHERE
			b.id = e.boek_id AND
			a.id = b.auteur_id AND
			c.id = b.categorie_id AND
			e.eigenaar_uid = '" . INGELOGD_UID . "'
		ORDER BY
			a.auteur, 
			b.titel
	");
	
	if (mysql_num_rows($result) == 0) {
?>
		<tr>
			<td colspan="7">
				U heeft nog geen boeken in de bibliotheek geregistreerd.
<?
	} else {
?>
		<tr class="tabelkop">
			<td>Titel&nbsp;
			<td>Auteur&nbsp;
			<td>&nbsp;
			<td>&nbsp;
			<td>Uitgeleend aan&nbsp;
			<td>Wijzig in&nbsp;
			<td>&nbsp;
<?
		$iCounter = 0;
		while ($row = mysql_fetch_row($result)) {
			list (
				$e_id, $eigenaar_uid, $uitgeleend_uid, 
				$b_id, $titel, $taal, $isbn, $uitgavejaar,
				$auteur,
				$categorie
			) = $row;
			
			if ($uitgavejaar == '0000') $uitgavejaar = '';
?>
			<tr id="row_<?=$iCounter?>">
				<td><?=mb_htmlentities($titel)?>&nbsp;
				<td><?=$auteur?>&nbsp;
				<td><a href="?action=wijzigen&exemplaarID=<?=$e_id?>">wijzigen</a>&nbsp;
				<td><a href="javascript:verwijderExemplaar('<?=$e_id?>', '<?=mb_htmlentities(addslashes($titel))?>');">verwijderen</a>&nbsp;
				<td><? if ($uitgeleend_uid != '') echo "<a href=\"http://csrdelft.nl/intern/profiel/$uitgeleend_uid\">" . getNameForUID($uitgeleend_uid) . "</a>"; ?>&nbsp;
				<td><? 
					if ($uitgeleend_uid != '') 	
						echo "<a href=\"javascript:boekTeruggekregen($e_id, '$titel')\">teruggekregen</a>&nbsp;"; 
					else 
						echo "<a href=\"?action=uitlenen&exemplaarID=$e_id\">uitgeleend</a>&nbsp;";
?>
				<td>&nbsp;
<?
			$iCounter++;
		}
	}
?>
	</table>