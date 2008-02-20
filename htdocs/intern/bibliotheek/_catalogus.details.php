<?
	require_once('inc.database.php');
	require_once('inc.common.php');
	
	$boekID = (int) getGET('boekID');
	
	# details boek ophalen en weergeven
	$result = db_query("
		SELECT 
			b.titel, b.taal, b.isbn, b.paginas, b.uitgavejaar, b.uitgeverij,
			a.auteur,
			c1.categorie, c2.categorie, c3.categorie
		FROM 
			biebboek b, biebauteur a, biebcategorie c1, biebcategorie c2, biebcategorie c3
		WHERE
			c1.id = c2.p_id AND c2.id = c3.p_id AND
			a.id = b.auteur_id AND
			c3.id = b.categorie_id AND
			b.id = $boekID
	");
	
	list($titel, $taal, $isbn, $paginas, $uitgavejaar, $uitgeverij, $auteur, $hoofdCategorie, $subCategorie, $categorie) = mysql_fetch_row($result);
	if ($uitgavejaar == "0000") $uitgavejaar = "";
?>
	<script language="Javascript" src="scripts/bieb.js"></script>
	<script language="Javascript" src="scripts/zxml.js"></script>
	<script language="Javascript">
		function verversExemplaren() {
			ajaxRequestToId('_catalogus.details.exemplaren.php?boekID=<?=$boekID?>', 'divExemplaren');
			return true;
		}
		
		function boekGeleend(exemplaar_id) {
			showPopupKop('Weet u zeker dat u dit boek wilt lenen?<br><a href="javascript:ajaxRequestAndDo(\'_lenen.geleend.php?exemplaarID=' + exemplaar_id + '\', verversExemplaren);">ja</a> &nbsp; <a href="javascript:hideMelding();">annuleren</a>', 'Bevestiging');
		}
				
		function boekTeruggegeven(exemplaar_id) {
			showPopupKop('Weet u zeker dat u dit boek terug hebt gegeven?<br><a href="javascript:ajaxRequestAndDo(\'_lenen.teruggegeven.php?exemplaarID=' + exemplaar_id + '\', verversExemplaren);">ja</a> &nbsp; <a href="javascript:hideMelding();">annuleren</a>', 'Bevestiging');
		}
		
		function boekTeruggekregen(exemplaar_id) {
			showPopupKop('Weet u zeker dat u dit boek terug hebt gekregen?<br><a href="javascript:ajaxRequestAndDo(\'_lenen.teruggekregen.php?exemplaarID=' + exemplaar_id + '\', verversExemplaren);">ja</a> &nbsp; <a href="javascript:hideMelding();">annuleren</a>', 'Bevestiging');
		}
		
		function verversBeschrijvingen() {
			ajaxRequestToId('_catalogus.beschrijvingen.php?boekID=<?=$boekID?>', 'divBeschrijvingen');
		}
		
		function nieuweBeschrijving() {
			ajaxRequestToId('_catalogus.addbeschrijving.form.php?boekID=<?=$boekID?>', 'divBeschrijvingToevoegen');
		}
		
		function addBeschrijving() {
			var qString = new queryString();
			
			qString.addParameter( 'boekID', '<?=$boekID?>' );
			qString.addParameter( 'beschrijving', document.getElementById('textBeschrijving').value );
			
			ajaxPostRequestOnOkDo('_catalogus.addbeschrijving.php', verversBeschrijvingen, qString.toString());
		}
		
		function editBeschrijving() {
			ajaxRequestToId('_catalogus.updatebeschrijving.form.php?boekID=<?=$boekID?>', 'divBeschrijvingToevoegen');
		}
		
		function updateBeschrijving() {
			var qString = new queryString();
			
			qString.addParameter( 'boekID', '<?=$boekID?>' );
			qString.addParameter( 'beschrijving', document.getElementById('textBeschrijving').value );
			
			ajaxPostRequestOnOkDo('_catalogus.updatebeschrijving.php', verversBeschrijvingen, qString.toString());
		}
		
		// beheerfunctie
		function verwijderExemplaar(exemplaar_id) {
			showPopupKop('Weet u zeker dat u dit exemplaar wilt verwijderen?<br><a href="javascript:ajaxRequestAndDo(\'_mijnboeken.verwijder.php?exemplaarID=' + exemplaar_id + '\', verversExemplaren);">ja</a> &nbsp; <a href="javascript:hideMelding();">annuleren</a>', 'Bevestiging');
		}
		
		// beheerfunctie
		function editExemplaar(exemplaar_id) {
			showPopupKop('Weet u zeker dat u dit exemplaar wilt verwijderen?<br><a href="javascript:ajaxRequestAndDo(\'_mijnboeken.verwijder.php?exemplaarID=' + exemplaar_id + '\', verversExemplaren);">ja</a> &nbsp; <a href="javascript:hideMelding();">annuleren</a>', 'Bevestiging');
		}
	</script>
	
	<div class="details">
	<table border="0" cellpadding="1" cellspacing="0">
	<tr>
		<td colspan="2" class="detailsKop"><?=$titel?> (<?=$auteur?>)&nbsp;</td>
	</tr>
	<tr class="detailsFirst">
		<td class="detailsCaption">Titel:</td>
		<td class="detailsInfo"><?=$titel?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">Auteur:</td>
		<td class="detailsInfo"><?=$auteur?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">Categorie:</td>
		<td class="detailsInfo"><?=$hoofdCategorie?> - <?=$subCategorie?> - <?=$categorie?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">Taal:</td>
		<td class="detailsInfo"><?=$taal?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">ISBN:</td>
		<td class="detailsInfo"><?=$isbn?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">Pagina's:</td>
		<td class="detailsInfo"><?=$paginas?>&nbsp;</td>
	</tr>
	<tr>
		<td class="detailsCaption">Jaar van uitgave:</td>
		<td class="detailsInfo"><?=$uitgavejaar?>&nbsp;</td>
	</tr>
	<tr class="detailsLast">
		<td class="detailsCaption">Uitgeverij:</td>
		<td class="detailsInfo"><?=$uitgeverij?>&nbsp;</td>
	</tr>
	</table>
	</div>
<?
	
	
	
	
	
	# exemplaren weergeven
?>
	<div id="divExemplaren" class="smalltext"><? include('_catalogus.details.exemplaren.php'); ?></div>
		
	<h2>Beschrijvingen</h2>
	
	<div class="smalltext" id="divBeschrijvingen">
		<? include('_catalogus.beschrijvingen.php'); ?>
	</div>