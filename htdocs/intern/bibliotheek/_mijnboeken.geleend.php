<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
?>
	<h2>Geleende boeken</h2>
	<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">
<?
	# boeken ophalen
	$result = db_query("
		SELECT 
			e.id, e.eigenaar_uid, 
			b.id, b.titel, 
			a.auteur
		FROM 
			biebexemplaar e, biebboek b, biebauteur a, biebcategorie c
		WHERE
			b.id = e.boek_id AND
			a.id = b.auteur_id AND
			c.id = b.categorie_id AND
			e.uitgeleend_uid = '" . INGELOGD_UID . "'
		ORDER BY
			a.auteur, 
			b.titel
	");
	
	if (mysql_num_rows($result) == 0) {
?>
		<tr>
			<td colspan="7">
				U heeft op dit moment geen boeken geleend.
<?
	} else {
?>
		<tr class="tabelkop">
			<td>Titel&nbsp;
			<td>Auteur&nbsp;
			<td>Geleend van&nbsp;
			<td>Wijzig in&nbsp;
			<td>&nbsp;
<?
		while ($row = mysql_fetch_row($result)) {
			list (
				$e_id, $eigenaar_uid, 
				$b_id, $titel,
				$auteur
			) = $row;
?>
			<tr>
				<td><?=$titel?>&nbsp;
				<td><?=$auteur?>&nbsp;
				<td><? if ($eigenaar_uid != '') echo "<a href=\"#TODO\">" . getNameForUID($eigenaar_uid) . "</a>"; ?>&nbsp;
				<td><? 
					if ($eigenaar_uid != '') echo "<a href=\"javascript:boekTeruggegeven($e_id, '$titel')\">teruggegeven</a>"; 
					else { ?><a href="#TODO">uitgeleend</a><? } ?>&nbsp;
				<td>&nbsp;
<?
		}
	}
?>
	</table>