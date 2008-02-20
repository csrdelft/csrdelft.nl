<?php
	require_once('inc.common.php');
	
	$boekID = (int) getGET('boekID');
	
	# beschrijvingen ophalen en weergeven
	$result = db_query("
		SELECT 
			bes.id, bes.schrijver_uid, bes.beschrijving
		FROM 
			biebboek b, biebbeschrijving bes
		WHERE
			b.id = bes.boek_id AND
			b.id = $boekID
		ORDER BY bes.toegevoegd
	");
?>
	<table border="0" cellpadding="1" cellspacing="0">
<?
	if (mysql_num_rows($result) == 0) {
?>
		<tr>
			<td colspan="4">
				Op dit moment zijn er geen beschrijvingen van dit boek<br>
<?
	} else {
		$iCounter = 0;
		while ($row = mysql_fetch_row($result)) {
			list (
				$bes_id, $bes_schrijver_uid, $bes_beschrijving
			) = $row;
?>
			<tr>
				<td valign="top"><b><?=getNameForUID($bes_schrijver_uid)?>: &nbsp;</b></span></td>
				<td valign="top"><?=$bes_beschrijving?></span></td>
			</tr>
<?
		}
	}
?>	</table>
	
	<div id="divBeschrijvingToevoegen">
	<br />
<?php
$result = db_query("
	SELECT
		id
	FROM
		biebbeschrijving 
	WHERE
		schrijver_uid = '" . INGELOGD_UID . "' AND boek_id = $boekID"
);
	
// check op eerdere beschrijving
if (mysql_num_rows($result) == 0) {
	echo '<a href="javascript:nieuweBeschrijving();">Een beschrijving toevoegen</a>';
} else {
	echo '<a href="javascript:editBeschrijving();">Beschrijving bewerken</a>';
}
?>
	</div>
