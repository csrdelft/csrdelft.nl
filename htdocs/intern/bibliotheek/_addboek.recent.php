<?
	require_once('inc.common.php');
	require_once('inc.database.php');
	
	# informatie ophalen
	$result = db_query("
		SELECT 
			e.id, e.eigenaar_uid, e.uitgeleend_uid,
			b.id, b.titel, b.taal, b.isbn, b.uitgavejaar,
			a.auteur
		FROM 
			biebexemplaar e, biebboek b, biebauteur a
		WHERE
			b.id = e.boek_id AND
			a.id = b.auteur_id AND
			e.eigenaar_uid = '" . INGELOGD_UID . "'
			AND e.toegevoegd > " . (time() - 86400) . "
	");

	if (mysql_num_rows($result) > 0) {
?>
		<h2>Recent toegevoegde boeken</h2>
	
		<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">
			<tr class="tabelkop">
				<td>Titel&nbsp;
				<td>Auteur&nbsp;
				<td>Taal&nbsp;
				<td>Uitgavejaar&nbsp;
<?
		$iCounter = 0;
		while ($row = mysql_fetch_row($result)) {
			list (
				$e_id, $eigenaar_uid, $uitgeleend_uid, 
				$b_id, $titel, $taal, $isbn, $uitgavejaar,
				$auteur,
			) = $row;
			
			if ($uitgavejaar == '0000') $uitgavejaar = '';
?>
			<tr id="row_<?=$iCounter?>">
				<td><?=$titel?>&nbsp;
				<td><?=$auteur?>&nbsp;
				<td><?=$taal?>&nbsp;
				<td><?=$uitgavejaar?>&nbsp;
<?						$iCounter++;
		}
?>
		</table>
<?
	}
?>