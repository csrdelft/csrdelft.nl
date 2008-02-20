<?php
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	# eventuele bevestigingen ophalen
	$result = db_query("
		SELECT
			e.id, 
			b.id, b.titel,
			a.auteur,
			bev.id, bev.uitgeleend_uid, bev.geleend_of_teruggegeven
		FROM 
			biebexemplaar e, biebboek b, biebauteur a, biebbevestiging bev
		WHERE
			b.id = e.boek_id AND
			a.id = b.auteur_id AND
			e.id = bev.exemplaar_id AND
			e.eigenaar_uid = '" . INGELOGD_UID . "'
	");
	
	if (mysql_num_rows($result) > 0) {
?>
	<div id="divBevestigingen" class="boekenlijst">
		<script language="Javascript" src="scripts/bieb.js"></script>
		<script language="Javascript" src="scripts/zxml.js"></script>
		<script language="Javascript">
			function verversBevestigingen() {
				ajaxRequestToId('_mijnboeken.bevestigingen.php', 'divBevestigingen');
				return true;
			}
		</script>
		
		<h2>Wijzigingen in status</h2>
		
		<table border="0" cellpadding="1" cellspacing="0" id="bevestigingenTabel">
			<tr>
				<td colspan="6">
					<script language="Javascript">
						showMelding('Er zijn door andere leden wijzigingen aangebracht met betrekking tot uw boeken. Kunt u deze wijzigingen s.v.p. bevestigen of ongedaan maken?');
					</script>
			<tr class="tabelkop">
				<td>Titel&nbsp;
				<td>Auteur&nbsp;
				<td>Bewering&nbsp;
				<td>Door&nbsp;
				<td>&nbsp;
				<td>&nbsp;
<?
		$melding = array( 
			'geleend' => 'geleend',
			'teruggegeven' => 'teruggegeven'
		);
		
		while ($row = mysql_fetch_row($result)) {
			list (
				$e_id, 
				$b_id, $titel, 
				$auteur, 
				$bev_id, $uitgeleend_uid, $geleend_of_teruggegeven 
			) = $row;
?>
			<tr>
				<td><?=$titel?>&nbsp;
				<td><?=$auteur?>&nbsp;
				<td><?=$melding[$geleend_of_teruggegeven]?>&nbsp;
				<td><?=getNameForUID($uitgeleend_uid)?>&nbsp;
				<td><a href="javascript:ajaxRequestAndDo('_mijnboeken.bevestig.php?bev_id=<?=$bev_id?>', verversBevestigingen);">bevestigen</a>&nbsp;
				<td><a href="javascript:ajaxRequestAndDo('_mijnboeken.draaiterug.php?bev_id=<?=$bev_id?>', verversBevestigingen);">ongedaan maken</a>&nbsp;
<?
		} 
?>
		</table>
	</div>
<?
	} 
?>