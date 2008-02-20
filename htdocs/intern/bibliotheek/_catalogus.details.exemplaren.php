<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
?>
	<h2>Exemplaren</h2>
	
	<table border="0" cellpadding="1" cellspacing="0">
<?
	$boekID = (int) getGET("boekID");
	
	# informatie exemplaren ophalen
	$result = db_query("
		SELECT 
			e.id, e.eigenaar_uid, e.uitgeleend_uid
		FROM 
			biebexemplaar e, biebboek b
		WHERE
			b.id = e.boek_id AND
			b.id = $boekID
	");
	if (mysql_num_rows($result) == 0) {
?>
		<tr class="tabelkop">
			<td colspan="4">Op dit moment zijn geen exemplaren van dit boek geregistreerd</td>
		</tr>
<?
	} else {
?>
		<tr class="tabelkop">
			<td>Eigenaar&nbsp;</td>
			<td>Uitgeleend aan&nbsp;</td>
			<td>Wijzig in&nbsp;</td>
			<? if (gebruikerIsAdmin()) { ?>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			<? } ?>
		</tr>
<?
		while ($row = mysql_fetch_row($result)) {
			list($e_id, $eigenaar_uid, $uitgeleend_uid) = $row;
?>
			<tr>
				<td class="detailsInfo"><a href="<?php echo CSR_ROOT.'intern/profiel/'.$eigenaar_uid.'">'; echo getNameForUID($eigenaar_uid); ?></a></td>
				<td class="detailsInfo"><?
					if ($uitgeleend_uid != "") {
						echo "<a href=\"#TODO\">" . getNameForUID($uitgeleend_uid) . "</a>";
					} else {
						echo "Niet uitgeleend";
					}
				?></td>
				<td class="detailsInfo"><? 
					if ($uitgeleend_uid == "") {
						if ($eigenaar_uid != INGELOGD_UID) {
							# stel (als lener) in op geleend
							echo "<a href=\"javascript:boekGeleend($e_id);\">geleend</a>";
						}
					} else {
						if ($eigenaar_uid == INGELOGD_UID) {
							# stel (als eigenaar) in op teruggekregen
							echo "<a href=\"javascript:boekTeruggekregen($e_id)\">teruggekregen</a>";
						} elseif ($uitgeleend_uid == INGELOGD_UID) {
							# wijzig (als lener) in teruggegeven
							echo "<a href=\"javascript:boekTeruggegeven($e_id)\">teruggegeven</a>";
						}
					}
				?>&nbsp;</td>
				<? if (gebruikerIsAdmin()) { ?>
					<td><a href="mijnboeken.php?action=wijzigen&exemplaarID=<?=$e_id?>">wijzigen</a>&nbsp;</td>
					<td><a href="javascript:verwijderExemplaar('<?=$e_id?>');">verwijderen</a>&nbsp;</td>
				<? } ?>
			</tr>
<?
		}
	}
?>
	</table>