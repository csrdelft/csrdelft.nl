<?php
	require_once('inc.html.php');
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$boekID = (int) getGET('boekID');
	
	if ($boekID != '') {
		$result = db_query("SELECT id FROM biebboek WHERE id = " . $boekID);
		if (mysql_num_rows($result) == 0) {
			printHeader();
?>
			<div id="main">
				Fout opgetreden: het boek dat u probeert te vinden is ons niet bekend.
			</div>
<?
			printFooter();
			exit;
		}
		
		printHeader();
?>
		<div id="main">
<?
			include("_catalogus.details.php");
?>
		</div>
<?
		printFooter();
	} else {
		printHeader();
?>	
		<script language="Javascript">
			var sorteerVolgorde = 'a.auteur, b.titel';
			var sorteerEerst = 'a.auteur';
			var sorteerVervolgens = 'b.titel';
			
			function verversCatalogus() {
				// begint bij 0, max = 50
				verversCatalogusLimit(0, 50);
			}
			
			function verversCatalogusLimit(limitStart, limitMax) {
				var nieuwePagina, vorigePagina;
				var qString = new queryString();
				
				qString.addParameter( 'titel', document.getElementById('filterTitel').value );
				qString.addParameter( 'auteur', document.getElementById('filterAuteur').value );
				qString.addParameter( 'categorie', document.getElementById('filterCategorie').value );
				qString.addParameter( 'taal', document.getElementById('filterTaal').value );
				qString.addParameter( 'start', limitStart );
				qString.addParameter( 'max', limitMax );
				qString.addParameter( 'sorteerOp', sorteerVolgorde );
				qString.addParameter( 'alleen_csr', document.getElementById('alleen_csr').checked );
				
				nieuwePagina = '_catalogus.view.php?' + qString;
				
				/* ververs de catalogus, mits de invoer is gewijzigd */
				if (nieuwePagina != vorigePagina)
					ajaxRequestReturnTo(nieuwePagina, 'catalogus');
				
				vorigePagina = nieuwePagina;
			}
			
			function sorteerOp(veld) {
				if (sorteerVervolgens != sorteerEerst) sorteerVervolgens = sorteerEerst;
				sorteerEerst = veld;
				sorteerVolgorde = sorteerEerst + ',' + sorteerVervolgens;
				verversCatalogus();
			}
		</script>
				
		<div id="main">
			<h2>Catalogus</h2>
			<div class="boekenlijst">
			<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">
				<tr>
					<tr class="tabelkop">
						<td>Titel&nbsp;</td>
						<td>Auteur&nbsp;</td>
						<td>Categorie&nbsp;</td>
						<td>Taal&nbsp;</td>
						<td>C.S.R.-bieb</td>
					</tr>
					<tr>
						<td><input type="text" id="filterTitel" size="30" onKeyUp="verversCatalogus();">&nbsp;</td>
						<td><input type="text" id="filterAuteur" size="30" onKeyUp="verversCatalogus();">&nbsp;</td>
						<td>
							<select id="filterCategorie" onChange="verversCatalogus();">
								<option value="0"><?
								$catResult = db_query("
									SELECT c3.id, c1.categorie, c2.categorie, c3.categorie
									FROM biebcategorie c1, biebcategorie c2, biebcategorie c3
									WHERE c2.p_id = c1.id AND c3.p_id = c2.id AND c1.p_id = 0
									ORDER BY c1.id, c2.id, c3.id
								");
								
								while (list($c_id, $c1_naam, $c2_naam, $c3_naam) = mysql_fetch_row($catResult)) {
									if (strlen($c1_naam) > 12) $c1_naam = substr($c1_naam, 0, 12) . "...";
									if (strlen($c2_naam) > 12) $c2_naam = substr($c2_naam, 0, 12) . "...";
									if (strlen($c3_naam) > 12) $c3_naam = substr($c3_naam, 0, 12) . "...";
									echo "<option value=\"$c_id\">$c1_naam - $c2_naam - $c3_naam\n";
								}
						?></select>&nbsp;
						</td>
						<td>
							<select id="filterTaal" onChange="verversCatalogus();">
								<option value="">
								<option value="Nederlands">Nederlands
								<option value="Engels">Engels
								<option value="Duits">Duits
								<option value="Frans">Frans
								<option value="Overig">Overig
							</select>&nbsp;
						</td>
						<td><input type="checkbox" id="alleen_csr" onClick="verversCatalogus();">&nbsp;</td>
					</tr>
			</table>
			</div>
			
			<div id="catalogus" class="boekenlijst">
				<? include('_catalogus.view.php'); ?>
			</div>
		
			<!-- Deze dialog moet ergens staan, voor meldingen -->
			<div id="box" class="dialog"></div>
		</div>
<?
		printFooter();
	}
?>