<?php // (eerste heette dit _addboek.form.php)
if( !isset($formulierModus) OR ($formulierModus != 'voegtoe' AND $formulierModus != 'wijzig') ) {
	// als er geen (juiste) formulierModus bekend is.
	$formulierModus = 'voegtoe';
} 

if($formulierModus == 'wijzig') {
	require_once('inc.database.php');
	
	$exemplaarID = (int) getGET('exemplaarID');
	
	// Het volgende is niet nodig, toch? 
//	if (	!isset($titel, $taal, $isbn, $paginas, $uitgavejaar, $auteur, $categorie) OR
//			$titel == "" && $taal == "" && $isbn == "" && $paginas == "" && $uitgavejaar == "" && $auteur == "" && $categorie == ""
//	) {
//		// informatie uit database ophalen; blijkbaar niet gepost
	
	$result = db_query("
		SELECT 
			b.titel, b.taal, b.isbn, b.paginas, b.uitgavejaar, b.uitgeverij,
			a.auteur,
			c.id
		FROM 
			biebboek b, biebauteur a, biebcategorie c, biebexemplaar e
		WHERE
			e.id = ".$exemplaarID." AND
			a.id = b.auteur_id AND
			c.id = b.categorie_id AND
			b.id = e.boek_id;
	");
	
	list ($titel, $taal, $isbn, $paginas, $uitgavejaar, $uitgeverij, $auteur, $categorie) = mysql_fetch_row($result);
//	}

// Niet nodig meer, toch?
//	if(!isset($invoerOK)) {
//		$invoerOK = false;
//	}

}

$formulierModus == 'voegtoe' ? $formId = 'boekToevoegForm' : $formId = 'boekWijzigForm';
echo '<form id="'.$formId.'">';

$formulierModus == 'voegtoe' ? $tableId = 'boekToevoegTabel': $tableId = 'boekWijzigTabel'; 
echo '<table border="0" cellpadding="1" cellspacing="0" id="'.$tableId.'">';
?>

	<tr>
		<td>Titel</td>
		<td>	<input type="text" name="titel" id="titel_autosuggest" size="40"
	<?php		if(isset($titel)) { echo ' value="'.mb_htmlentities($titel).'"'; }
				echo ' />';
				if(isset($invoerOK, $titel) AND !$invoerOK AND $titel == "") {
					printFormFoutSpan('Titel niet ingevoerd');
				}
	?>	</td>
	</tr>
	<tr>
		<td>Auteur</td>
		<td><input type="text" name="auteur" id="auteur_autosuggest" size="40"
	<?php		if(isset($auteur)) { echo ' value="'.mb_htmlentities($auteur).'"'; }
				echo ' />';
				if (isset($invoerOK, $auteur) AND !$invoerOK AND $auteur == "") {
					printFormFoutSpan('Auteur niet ingevoerd');
				}
	?>	</td>
	</tr>
	<tr>
		<td>Categorie</td>
		<td>
	<?php 
		if(!isset($categorie)) { $categorie = null; }
		printCategorieSelector($categorie);
		if( isset($invoerOK, $categorie) AND !$invoerOK AND ($categorie == 0 || $categorie == "") ) {
			printFormFoutSpan('Geen categorie geselecteerd');
		}
		
		// alleen voor bibliothecaris
		if (gebruikerIsAdmin()) { ?></td>
	</tr>
	<tr>
		<td>Code</td>
		<td>	<input type="text" name="code" size="10"
		<?php	if(isset($code)) { echo 'value="'.$code.'"'; }
				echo ' />';
				if (isset($invoerOK, $code) AND !$invoerOK AND $code == "") {
					printFormFoutSpan('Code niet ingevoerd');
				}
			} ?>
		</td>
	</tr>
	<tr>
		<td>Taal</td>
		<td>
			<?php
			echo '<select name="taal">';
			// options printen
			$talen = getTalenArray();
			foreach($talen as $taalString) {
				$selected = '';
				if(isset($taal) AND in_array($taal, $talen) AND $taal == $taalString) {
					$selected = ' selected="selected"';
				}
				echo '<option value="'.$taalString.'"'.$selected.'>'.$taalString."\n";
			}
			echo '</select>';

			if (	isset($invoerOK, $taal) AND
					!$invoerOK AND
					!in_array($taal, $talen)
			) {
				printFormFoutSpan('Onjuiste taal ingevoerd');
			}	?>
		</td>
	</tr>
	<tr>
		<td>ISBN</td>
		<td>	<input type="text" name="isbn" size="20"
		<?php	if(isset($isbn)) { echo ' value="'.mb_htmlentities($isbn).'"'; }
				echo ' />';
		?>	
		</td>
	</tr>
	<tr>
		<td>Pagina's</td>
		<td><input type="text" name="paginas" size="4"
		<?php	if(isset($paginas)) { echo 'value="'.$paginas.'"'; }
				echo ' />';
				if (	isset($invoerOK) AND
						!$invoerOK AND
						(!empty($paginas) AND !ereg('^[0-9]{1,4}$', $paginas))
				) {
					printFormFoutSpan('Incorrect aantal pagina\'s');
				} ?>
		</td>
	</tr>
	<tr>
		<td>Uitgavejaar</td>
		<td><input type="text" name="uitgavejaar" size="4"
		<?php	if(isset($uitgavejaar)) { echo ' value="'.$uitgavejaar.'"'; }
				echo ' />';
				if( isset($invoerOK, $uitgavejaar) AND
					!$invoerOK AND
					($uitgavejaar != '' AND !ereg('^[0-9]{4}$', $uitgavejaar))
				) {
					printFormFoutSpan('Incorrect jaartal');
				}
		?>
		</td>
	</tr>
	<tr>
		<td>Uitgeverij</td>
		<td><input type="text" name="uitgeverij" size="40"
		<?php	if(isset($uitgeverij)) { echo ' value="'.mb_htmlentities($uitgeverij).'"'; }
				echo ' />';
		?>	
		</td>
	</tr>

	<?php
	if($formulierModus != 'wijzig') {
	?>
	<tr>
		<td>Beschrijving / opmerking</td>
		<td>
			<textarea name="beschrijving" rows="5" cols="40"><?php	if(isset($beschrijving)) { echo mb_htmlentities($beschrijving); }
			?></textarea>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td>
			<?php
			if($formulierModus == 'voegtoe'){
				$jsFunctienaamSubmit = 'addBoek()';
				$knopTekst = 'Voeg toe';
			} else {
				$jsFunctienaamSubmit = 'wijzigBoek()';
				$knopTekst = 'Wijzig';
			}
			echo '<a href="javascript:'.$jsFunctienaamSubmit.';">'.$knopTekst.'</a>';
			?>
		</td>
	</tr>
</table>
</form>

<script language="javascript" type="text/javascript" src="scripts/autocomplete.js"></script>
<script language="javascript" type="text/javascript">
    auteurs = [<?
		printAuteursForAutoSuggest();
	?>];
	
    titels = [<?
		printTitelsForAutoSuggest();
	?>];

    AutoComplete_Create('auteur_autosuggest', auteurs);
    AutoComplete_Create('titel_autosuggest', titels);
</script>

<?php
function printFormFoutSpan($tekst) {
	echo '<span class="formFout">'.$tekst.'</span>';
}
?>
