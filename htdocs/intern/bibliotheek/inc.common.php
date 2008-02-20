<?php
	require_once('include.config.php');
	require_once('inc.database.php');
	require_once('functies.verwijderen.php');
	
	$lid=Lid::get_lid();
	$uid=$lid->getUid();
	define('INGELOGD_UID',$uid );
	if(!$lid->hasPermission('P_BIEB_READ')){
		printHeader();
?>
			<div id="main">
				<h2>Bibliotheek</h2>
				<p>Welkom op de thuispagina van de C.S.R.-gedistribueerde bibliotheek. <br>
				U bent nog niet ingelogd. Om deze pagina te bekijken, moet u ingelogd zijn. <br>
				<div id="inloggen">
				<?php
					if(isset($_SESSION['auth_error'])) {
						echo '<span class="waarschuwing">Ongeldige gebruiker of wachtwoord!</span>';
						// Ten slotte even unsetten, zodat we geen last meer hebben van de melding.
						unset($_SESSION['auth_error']);
					}
				?>
					<form id="frm_login" action="/login.php" method="post">
					<p style="display: inline;">
						<input type="hidden" name="url" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
						<input type="text" name="user" class="login" value="Naam" onfocus="this.value=''" />
						<input type="password" name="pass" class="login" />
						<input type="submit" name="submit" class="login-submit" value="ok" /><br />
						<input type="checkbox" name="checkip" class="login-checkip" value="true" id="login-checkip" checked="checked" />
						<label for="login-checkip">Koppel login en IP-adres</label><br />
					</p>
					</form>
				</div>
			</div>
<?
		printFooter();
		exit;
	}
	
	function gebruikerIs($uid) {
		return ($uid == INGELOGD_UID);
	}
	
	function gebruikerMag($uid) {
		return (($uid == INGELOGD_UID) || gebruikerIsAdmin());
	}
	
	function gebruikerIsAdmin() {
		$lid=Lid::get_lid();
		return $lid->hasPermission('P_BIEB_MOD');
	}
	
	function getcategorieresult() {
		return db_query("SELECT id, categorie FROM biebcategorie WHERE p_id <> 0");
	}
	
	function printCategorieSelector($huidigeCategorieID = 0) {
?>
		<select name="categorie">
			<option value="0">Selecteer een categorie<?
			$catResult = db_query("
				SELECT
						c3.id, c1.categorie, c2.categorie, c3.categorie
				FROM
						biebcategorie c1, biebcategorie c2, biebcategorie c3
				WHERE
						c2.p_id = c1.id AND c3.p_id = c2.id AND c1.p_id = 0
				ORDER BY
						c1.id, c2.id, c3.id;
			");
			
			while (list($c_id, $c1_naam, $c2_naam, $c3_naam) = mysql_fetch_row($catResult)) {
				$printSelected = "";
				if ($c_id == $huidigeCategorieID) $printSelected = " selected=\"selected\"";
				echo "<option value=\"$c_id\"{$printSelected}>$c1_naam - $c2_naam - $c3_naam\n";
			}
?>
		</select>
<?
	}
	
	function printTitelsForAutoSuggest() {
		$result = db_query("SELECT titel FROM biebboek ORDER BY titel");
		
		# print alle auteurs met komma's ertussen
		for ($komma = false; list($titel) = mysql_fetch_row($result); $komma = true) {
			if ($komma) echo ", ";
			echo "'" . addSlashes($titel) . "'";
		}
	}
	
	function printAuteursForAutoSuggest() {
		$result = db_query("SELECT auteur FROM biebauteur ORDER BY auteur");
		
		# print alle auteurs met komma's ertussen
		for ($komma = false; list($auteur) = mysql_fetch_row($result); $komma = true) {
			if ($komma) echo ", ";
			echo "'" . addSlashes($auteur) . "'";
		}
	}
	
	function iAm($name_to_match) {
		return (substr($_SERVER["PHP_SELF"], -(strlen($name_to_match)), (strlen($name_to_match))) == $name_to_match);
	}
	
	/*
	 * Leden-functies
	 */
	
	function getNameForUID($uid, $nicknames = false) {
		if ($uid == "csr") return "Bibliotheek";
		
		$result = db_query("
			SELECT uid, nickname, voornaam, tussenvoegsel, achternaam 
			FROM lid 
			WHERE uid = '$uid'
		");
		
		# naam niet gevonden
		if (mysql_num_rows($result) == 0) return 'Onbekend';
		
		list($uid, $nickname, $voornaam, $tussenvoegsel, $achternaam) = mysql_fetch_row($result);
		
		# naam gevonden: retourneren
		return maakNaamVan($nickname, $voornaam, $tussenvoegsel, $achternaam, $nicknames);
	}
	
	function maakNaamVan($nickname, $voornaam, $tussenvoegsel, $achternaam, $nicknames = false) {
		# voornaam
		$tempNaam = $voornaam;
		
		# eventueel een tussenvoegsel erin
		if ($tussenvoegsel != '') $tempNaam .= ' ' . $tussenvoegsel;
		
		# achternaam
		$tempNaam .= ' ' . $achternaam;
		
		# eventueel nickname toevoegen
		if ($nicknames && ($nickname != '')) $tempNaam .= " ($nickname)";
		
		return $tempNaam;
	}
	
	function getLeden($gedeelte = '') {
		$gedeelte = db_escape($gedeelte);
		
		$result = db_query("
			SELECT uid, nickname, voornaam, tussenvoegsel, achternaam 
			FROM lid 
			WHERE 
				(
					status = 'S_LID' OR
					status = 'S_GASTLID' OR
					status = 'S_NOVIET' OR
					status = 'S_KRINGEL'
				) AND
				(
					nickname LIKE '%$gedeelte%' OR 
					voornaam LIKE '%$gedeelte%' OR 
					achternaam LIKE '%$gedeelte%'
				)
			ORDER BY
				voornaam, achternaam
		");
		
		$returnValue = array();
		for ($i=0; list($uid, $nickname, $voornaam, $tussenvoegsel, $achternaam) = mysql_fetch_row($result); $i++) {
			
			$returnValue[$i][0] = $uid;
			$returnValue[$i][1] = maakNaamVan($nickname, $voornaam, $tussenvoegsel, $achternaam, true);
		}
		
		return $returnValue;
	}
	
	function getGET($key){
		if(isset($_GET[$key])){
			return $_GET[$key];
		}else{
			return null;
		}
	}
	
	function getTalenArray() {
		return array('Nederlands', 'Engels', 'Duits', 'Frans', 'Overig');
	}
?>