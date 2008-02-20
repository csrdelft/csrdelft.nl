<?php
	require_once('inc.html.php');
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$exemplaarID = (int) getGET('exemplaarID');
	$action = getGET('action');
	switch($action) {
		case "uitlenen":
			printHeader();
			
			$result = db_query("
				SELECT titel, auteur 
				FROM biebboek, biebauteur, biebexemplaar 
				WHERE biebboek.id = biebexemplaar.boek_id AND biebboek.auteur_id = biebauteur.id AND biebexemplaar.id = $exemplaarID
			");
			
			if (mysql_num_rows($result) > 0) list($titel, $auteur) = mysql_fetch_row($result);
			
			$boek = $auteur . " - " . $titel;
?>	
			<script language="Javascript">
				function verversLeden() {
					var nieuweTekst, vorigeTekst;
					
					nieuweTekst = document.getElementById('filterTekst').value;
					
					/* ververs de lijst, mits de invoer is gewijzigd */
					if (nieuweTekst != vorigeTekst) 
						ajaxRequestReturnTo('_mijnboeken.ledenlijst.php?deelNaam=' + nieuweTekst, 'divLedenlijst');
					
					vorigeTekst = nieuweTekst;
				}
				
				function doorNaarMijnBoeken() {
					document.location.href = '?';
				}
				
				function uitlenenAan(uid) {
					ajaxRequestOnOkDo('_lenen.uitgeleend.php?exemplaarID=<?=$exemplaarID?>&lenerUID=' + uid, doorNaarMijnBoeken);
				}
			</script>
		
			<div id="main">
				<h2>Boek uitlenen</h2>
				Selecteer een persoon om het boek <b><?=mb_htmlentities($boek)?></b> aan uit te lenen.<br>
				<br>
				
				<h2>Personen</h2>
				<div class="boekenlijst">
					Zoek: <input type="text" id="filterTekst" size="30" onKeyUp="verversLeden();">
				</div>
				
				<script language="Javascript">
					document.getElementById('filterTekst').focus();
				</script>
				
				<div id="divLedenlijst" class="boekenlijst"><? include("_mijnboeken.ledenlijst.php"); ?></div>
			</div>
<?
			printFooter();
		break;
	
		case "wijzigen":
			printHeader();
			
			if ($exemplaarID == "") {
				echo "Fout opgetreden: geen exemplaar opgegegeven";
				printFooter();
				exit;
			}
?>
			<script language="Javascript">
				function klusQString() {
					var oForm = document.getElementById('boekWijzigForm');
					var qString = new queryString();
					qString.addParameter( 'exemplaarID', '<?php echo $exemplaarID; ?>' );
					
					for (var i=0 ; i < oForm.elements.length; i++) {
						qString.addParameter( oForm.elements[i].name, oForm.elements[i].value );
					}
					
					return qString;
				}
				
				function wijzigBoek() {
					qString = klusQString();
					
					/* Controleren en toevoegen */
					ajaxRequestFormOnOkDo(
						'_wijzigboek.controle.php?' + qString, 
						wijzigBoekConfirmed,
						'divBoekWijzigForm'
					);
				}
				
				function wijzigBoekConfirmed() {
					qString = klusQString();
					
					ajaxRequestOnOkDo('_wijzigboek.php?' + qString, terugNaarMijnBoeken);
				}
				
				function terugNaarMijnBoeken() {
					document.location.href = '?melding=Boek%20succesvol%20gewijzigd';
					return false;
				}
			</script>
			
			<div id="main">
				<div id="divBoekWijzigForm" class="boekenlijst">
					<h2>Mijn boeken - gegevens wijzigen</h2>
<?php
					$formulierModus = 'wijzig';
					include("_boek.form.php");
?>
				</div>
			</div>
<?
		printFooter();
		break;

		case "nieuw":
			printHeader();
?>
			<script language="Javascript">
				function verversRecentToegevoegd() {
					ajaxRequestToId('_addboek.recent.php', 'divEigenBoeken');
					return true;
				}
				
				function addBoek() {
					var oForm = document.getElementById('boekToevoegForm');
					var qString = new queryString();
					for (var i=0 ; i < oForm.elements.length; i++) {
						qString.addParameter( oForm.elements[i].name, encodeURI(oForm.elements[i].value) );
					}
					
					/* Controleren en toevoegen */
					ajaxRequestFormOnOkDo(
						'_addboek.controle.php?' + qString, 
						addBoekConfirmed, 
						'divBoekToevoegForm'
					);
				};
				
				function addBoekConfirmed() {
					var oForm = document.getElementById('boekToevoegForm');
					var qString = new queryString();
					
					for (var i=0 ; i < oForm.elements.length; i++) {
						qString.addParameter( oForm.elements[i].name, encodeURI(oForm.elements[i].value) );
					}
					
					ajaxRequestAndDo('_addboek.php?' + qString, verversRecentToegevoegd);
				}
			</script>
		
			<div id="main">
				<div id="divBoekToevoegForm" class="boekenlijst">
					<h2>Mijn boeken - boek toevoegen</h2>
<?
					$formulierModus = 'voegtoe';
					include("_boek.form.php");  	
?>
					<script language="javascript">
						document.getElementById('titel_autosuggest').focus();
					</script>
				</div>
				
				<div id="divEigenBoeken" class="boekenlijst">
<?
					include("_addboek.recent.php");
?>
				</div>
			</div>
<?
		printFooter();
		break;
		
		
		/*
		 * Hoofdpagina
		 * -----------
		 * Overzicht van eigen boeken en geleende boeken, met mogelijkheid voor
		 * - boeken toevoegen
		 * - boeken wijzigen
		 * - boeken verwijderen
		 * - melden dat een boek is uitgeleend
		 * - melden dat een uitgeleend boek is teruggekregen
		 * - melden dat een geleend boek is teruggegeven
		 */
	 	default:
			printHeader();
?>
		
			<script language="Javascript" src="scripts/bieb.js"></script>
			<script language="Javascript" src="scripts/zxml.js"></script>
			<script language="Javascript">
				function verversGeleend() {
					ajaxRequestToId('_mijnboeken.geleend.php', 'divGeleend');
					return true;
				}
				
				function verversBoekenlijst() {
					ajaxRequestToId('_mijnboeken.boekenlijst.php', 'divBoekenlijst');
					return true;
				}
				
				function boekTeruggegeven(exemplaar_id, titel) {
					showPopupKop('Weet u zeker dat u het boek \'' + titel + '\' terug hebt gegeven?<br><a href="javascript:ajaxRequestAndDo(\'_lenen.teruggegeven.php?exemplaarID=' + exemplaar_id + '\', verversGeleend);">ja</a> &nbsp; <a href="javascript:hidePopup();">annuleren</a>', 'Bevestiging');
				}
				
				function boekTeruggekregen(exemplaar_id, titel) {
					showPopupKop('Weet u zeker dat u het boek \'' + titel + '\' terug hebt gekregen?<br><a href="javascript:ajaxRequestAndDo(\'_lenen.teruggekregen.php?exemplaarID=' + exemplaar_id + '\', verversBoekenlijst);">ja</a> &nbsp; <a href="javascript:hidePopup();">annuleren</a>', 'Bevestiging');
				}
				
				function verwijderExemplaar(exemplaar_id, titel) {
					showPopupKop('Weet u zeker dat u het boek \'' +  titel + '\' wilt verwijderen?<br><a href="javascript:ajaxRequestAndDo(\'_mijnboeken.verwijder.php?exemplaarID=' + exemplaar_id + '\', verversBoekenlijst);">ja</a> &nbsp; <a href="javascript:hidePopup();">annuleren</a>', 'Bevestiging');
				}
			</script>
			<div id="main">
				<? include("_mijnboeken.bevestigingen.php"); ?>
				
				<div id="divBoekenlijst" class="boekenlijst"><? include("_mijnboeken.boekenlijst.php"); ?></div>
				
				<div id="divGeleend" class="boekenlijst"><? include("_mijnboeken.geleend.php"); ?></div>
					
			</div>
<?
		
			printFooter();
		break;
	}
?>