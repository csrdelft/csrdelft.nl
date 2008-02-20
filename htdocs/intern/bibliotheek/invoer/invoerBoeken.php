<?php
	require_once("../inc.database.php");
	
	if (isset($_POST["categorielijst"])) {
		$worklist = $_POST["categorielijst"]; //stripslashes($_POST["categorielijst"]);
		
		$metbr = nl2br($worklist);
		$regels = explode('<br />', $metbr);
		
		foreach ($regels as $regel) {
			list($code, $titel, $auteur, $jaar, $uitgeverij, $paginas, $isbn) = explode(';', $regel);
			$code = trim($code);
			$categorie_id = substr($code, 0, 3);
			
			# evt komma's terugzetten
			$titel = str_replace('-,-', ';', $titel);
			
			# eerst boek toevoegen als het nieuw is. Na dit stukje is $boek_id bekend
			if (db_firstCell("SELECT COUNT(*) FROM biebboek WHERE titel = '$titel' AND code = '$code'") == 0) {
				# auteur toevoegen als hij nieuw is. Na dit stukje is $auteur_id bekend
				if (db_firstCell("SELECT COUNT(*) FROM biebauteur WHERE auteur = '$auteur'") == 0) {
					db_query("INSERT INTO biebauteur SET auteur='$auteur'");
					
					# id van auteur opvragen
					$auteur_id = mysql_insert_id();
				} else {
					# auteur_id opvragen
					$auteur_id = db_firstCell("SELECT id FROM biebauteur WHERE auteur = '$auteur'");
				}
				
				# boek toevoegen
				db_query("INSERT INTO biebboek SET code='$code', titel='$titel', categorie_id=$categorie_id, auteur_id=$auteur_id, uitgavejaar='$jaar', uitgeverij='$uitgeverij', paginas='$paginas', isbn='$isbn'");
				
				# id van boek opvragen
				$boek_id = mysql_insert_id();
			} else {
				# id van boek opvragen
				$boek_id = db_firstCell("SELECT id FROM biebboek WHERE titel = '$titel' AND code = '$code'");
			}
			
			# exemplaar toevoegen
			$tijdStamp = time();
			db_query("INSERT INTO biebexemplaar SET boek_id=$boek_id, eigenaar_uid='bieb', toegevoegd='$tijdStamp'");
		}
	} else {
?>
		<h2>Boeken toevoegen</h2>
		Op deze pagina kunt u boeken toevoegen. 
		<ul>
			<li>Zorg dat de boeken in een excel-document staan met kolommen: <i>Code	Titel	Auteur	Jaar	Uitgeverij	Paginas	ISBN</i></li>
			<li>Zorg dat er geen headerrij boven staat</li>
			<li>Vervang alle puntkomma's door '-,-'. Dit is omdat de puntkomma's de scheiding tussen kolommen aan gaan geven. In dit script worden alle voorkomens van '-,-' weer omgezet in puntkomma's.</li>
			<li>Sla het bestand op als .csv-bestand. Zorg dat de rijen gescheiden worden door puntkomma's. </li>
			<li>Open het bestaan m.b.v. notepad en plak het in onderstaand tekstveld.</li>
			<li>Voer in!</li>
		</ul>
		<form action="?" method="post">
			<textarea cols="40" rows="15" name="categorielijst"></textarea><br>
			<input type="submit" value="Invoeren">
		</form>
		</td>
	</tr>
</table>
</body>
</html>
<?
	}
?>