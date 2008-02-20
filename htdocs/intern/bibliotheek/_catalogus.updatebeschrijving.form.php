<?php
// De requires doen we even hier bovenaan; anders komen we in de knoei met de session_start.
require_once('include.config.php');
require_once('inc.common.php');
?>
<h2>Wijzig beschrijving</h2>

<form id="beschrijvingToevoegForm">
<table border="0" cellpadding="1" cellspacing="0" id="boekToevoegTabel">
	<tr>
		<td>Beschrijving / opmerking&nbsp;</td>
		<td>
			<textarea id="textBeschrijving" rows="8" cols="60"><?php
				// BoekID ophalen
				$boekID = getGET('boekID');
				if( $boekID !== null ) {
					// Nog even een int van maken.
					$boekID = (int) $boekID;
					
					// De query die de beschrijving ophaalt.
					$query = "
						SELECT
							beschrijving
						FROM
							biebbeschrijving
						WHERE
							boek_id = ".$boekID."
						AND
							schrijver_uid = '".INGELOGD_UID."'
					";
					$resultBeschrijving = db_query($query);
					if(mysql_num_rows($resultBeschrijving) == 1) { // Als er één resultaat is
						$arrayBeschrijving = mysql_fetch_array($resultBeschrijving);
						$beschrijving = $arrayBeschrijving['beschrijving'];
					
						// De beschrijving echo-en (in het textarea).
						echo $beschrijving;
					}
				}?></textarea>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a href="javascript:updateBeschrijving();">Wijzig</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:verversBeschrijvingen();">Annuleren</a></td>
</table>
</form>