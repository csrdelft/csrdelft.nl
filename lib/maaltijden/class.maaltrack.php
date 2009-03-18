<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdvoorkeurcontent.php
# -------------------------------------------------------------------
# MaalTrack bevat overkoepelende functies voor maaltijden
# - maaltijd aanmaken
# - maaltijd verwijderen
# - maaltijdenlijst opvragen
# - abonnementen aan/uitzetten
# -------------------------------------------------------------------


require_once("maaltijden/class.maaltijd.php");

class MaalTrack {
	# MySQL connectie
	var $_db;


	# evt. foutboodschap
	var $_error = '';
	var $_proxyerror = '';

	function __construct() {
		$this->_db =MySql::instance();
	}

	function getError() { $error = $this->_error; $this->_error = ""; return $error; }
	function getProxyError() { $proxyerror = $this->_proxyerror; $this->_proxyerror = ""; return $proxyerror; }

	# datum - timestamp wanneer de maaltijd is
	# tekst - omschrijving/menu van de maaltijd
	# abosoort - enum-waarde van een abo dat geldt voor deze maaltijd
	# tp = tafelpraeses-uid
	# max - maximaal aantal inschrijvingen
	function addMaaltijd($datum, $tekst, $abosoort, $tp, $koks, $afwassers, $theedoeken, $max = MAX_MAALTIJD) {
		$datum = (int)$datum;
		$max = (int)$max;
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		$koks=abs((int)$koks);
		$afwassers=abs((int)$afwassers);
		$theedoeken=abs((int)$theedoeken);

		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateMaaltijd($datum, $tekst, $abosoort, $tp, $max)){
			return false;
		}

		# voeg de maaltijd toe en geef het maalid terug, of false als het  niet gelukt is.
		$maaltijd="
			INSERT INTO
				maaltijd
			(
				datum, tekst, abosoort, max, tp, koks, afwassers, theedoeken
			)VALUES(
				'".$datum."', '".$tekst."', '".$abosoort."', '".$max."',
				'".$tp."', '".$koks."', '".$afwassers."', '".$theedoeken."'
			);";

		if (!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query";
			return false;
		}else{
			$maaltijd = new Maaltijd($this->_db->insert_id());
			# ook maar meteen even hertellen, dan zijn de mensen die daar blij van worden weer extra blij...
			$maaltijd->recount();
			return $maaltijd->getMaalId();
		}
	}

	# bestaande maaltijd bewerken. Niet veel verschil met addMaaltijd, behalve dat hier nog even
	# gekeken wordt of de maaltijd wel bestaat.
	function editMaaltijd($maalid, $datum, $tekst, $abosoort, $tp, $koks, $afwassers, $theedoeken, $max=MAX_MAALTIJD){
		if($maalid!=(int)$maalid){
			$this->_error="Ongeldig maaltijdID opgegeven.";
			return false;
		}
		if(!$this->isMaaltijd($maalid)){
			$this->_error="Opgegeven maaltijd bestaat niet.";
			return false;
		}
		$datum = (int)$datum;
		$max = (int)$max;
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		$koks=abs((int)$koks);
		$afwassers=abs((int)$afwassers);
		$theedoeken=abs((int)$theedoeken);

		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateMaaltijd($datum, $tekst, $abosoort, $tp, $max)){
			return false;
		}
		$maaltijd="
			UPDATE
				maaltijd
			SET
				datum=".$datum.",
				tekst='".$tekst."',
				abosoort='".$abosoort."',
				tp='".$tp."',
				koks='".$koks."',
				afwassers='".$afwassers."',
				theedoeken='".$theedoeken."',
				max=".$max."
			WHERE
				id=".$maalid."
			LIMIT 1;";
		if(!$this->_db->query($maaltijd)){
			$this->_error="Er is iets mis met de database/query.";
			return false;
		}else{
			$maaltijd = new Maaltijd ($maalid);
			# ook maar meteen even hertellen, dan zijn de mensen die daar blij van worden weer extra blij...
			$maaltijd->recount();
			return $maalid;
		}
	}
	# deze methode valideert de gemeenschappelijke waarden van addMaaltijd en editMaaltijd.
	# controle op specifieke dingen voor editMaaltijd gebeurt nog in de methode zelf.
	function validateMaaltijd($datum, $tekst, $abosoort, $tp, $max){
		# controleer of de datum niet in het verleden ligt
		if ($datum < time()) {
			$this->_error = "Het tijdstip van de maaltijd moet in de toekomst liggen";
			return false;
		}

		# tekst max 200 karakters
		if (!is_utf8($tekst)) {
			$this->_error = "De omschrijving bevat ongeldige tekens.";
			return false;
		}


		# kijk of $tp voorkomt in de ledenlijst
		if($tp != "" AND !Lid::exists($_POST['tp'])){
			$this->_error = "De tafelpraeses moet voorkomen in de ledenlijst.";
			return false;
		}

		# kijk of $abosoort voorkomt in tabel maaltijdabosoort
		if (!array_key_exists($abosoort, $this->getAbos())) {
			$this->_error = "Er is geen bestaande abonnementsvorm opgegeven.";
			return false;
		}

		# controleer of het maximum aantal > 0 en <= MAX_MAALTIJD is
		if ($max <= 0 or $max > MAX_MAALTIJD) {
			$this->_error = "Het maximaal aantal eters moet tussen 1 en " . MAX_MAALTIJD . " zijn.";
			return false;
		}
		# kijk of een gekozen abo niet meteen meer inschrijvingen oplevert dan het maximum wat ingesteld wordt
		if ($abosoort != "" and $this->getAboCount($abosoort) > $max) {
			$this->_error = "Het gekozen abonnement levert meer inschrijvingen op dan het maximaal ingestelde aantal.";
			return false;
		}
		return true;
	}
	function removeMaaltijd($maalid) {
		if (!is_numeric($maalid)) {
			$this->_error = "Gebruik een numerieke maaltijd-id waarde om te verwijderen";
			return false;
		}
		# kijk of de maaltijd wel bestaat.
		if(!$this->isMaaltijd($maalid)){
			$this->_error = "Deze maaltijd bestaat niet";
			return false;
		}

		# verwijder alle aan/afmeldingen voor deze maaltijd
		# ...van leden met de bijbehoorende gasten.
		$aanmeldingen="DELETE FROM maaltijdaanmelding WHERE maalid=".$maalid;

		# verwijder de maaltijd zelf
		$maaltijd="DELETE FROM maaltijd WHERE id=".$maalid;

		return $this->_db->query($aanmeldingen) AND $this->_db->query($maaltijd);
	}


	# haalt één enkele maaltijd op ter bewerking
	function getMaaltijd($maalid){
		$maalid=(int)$maalid;
		if($maalid==0){
			$this->_error="Geen geldig maaltijd-id";
			return false;
		}
		$sMaaltijdQuery="
			SELECT
				id, datum, gesloten, tekst, abosoort, max, aantal,
				tp, koks, afwassers, theedoeken
			FROM
				maaltijd
			WHERE
				id=".$maalid."
			LIMIT 1;";
		$rMaaltijd=$this->_db->query($sMaaltijdQuery);
		$aMaal=$this->_db->next($rMaaltijd);

		return $aMaal;
	}

	# haalt maaltijden uit de maaltijdentabel op, voor uitgebreidere info
	# voor in de kolommen op de maaltijdencontent pagina, zie getMaaltijden hieronder
	# als de gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	public static function getMaaltijdenRaw($van = 0, $tot = 0, $mootfilter = true) {
		$lid=LoginLid::instance();
		$db=MySql::instance();
		# kijk in db en haal alle maaltijden op waarbij de begintijd
		# na $van is, en voor $tot

		# meestal zal voor $van time() gebruikt worden, als er niets is opgegeven
		# dan wordt ook de huidige tijd gebruikt
		if ($van == 0) $van = time();

		# als $tot niet is opgegeven, of 0 is, dan worden alle maaltijden vanaf
		# van teruggegeven, gesorteerd op tijd
		$tot = (int)$tot;
		$totsql = ($tot != 0) ? "datum < '".$tot."'" : "1";

		# mootfilter
		if(!$lid->hasPermission('P_MAAL_MOD')){
			if($mootfilter === true){ $moot = $lid->getLid()->getMoot(); }
		}else{
			$mootfilter=false;
		}

		$maaltijden = array();
		$sMaaltijdQuery="
			SELECT
				id, datum, gesloten, tekst, abosoort, max, aantal, tp, koks, afwassers, theedoeken
			FROM
				maaltijd
			WHERE
				datum > '".$van."' AND ".$totsql."
			ORDER BY
				datum ASC;";
		$result=$db->select($sMaaltijdQuery);
		if (($result !== false) and $db->numRows($result) > 0) {
			while ($record = $db->next($result)) {
				if(!($mootfilter===true AND preg_match("/(MOOT|UBER)[^{$moot}]{1}/", $record['abosoort']))){
					$maaltijden[] = $record;

				}
			}
		}
		# id, datum, gesloten, tekst, abosoort, max, aantal, tp
		return $maaltijden;
	}

	function getAboTekst($abosoort = '') {
		# abotekst: tekst van het abo dat van toepassing is of ''
		if ($abosoort == '') return '';
		$result = $this->_db->select("SELECT tekst FROM maaltijdabosoort WHERE abosoort = '{$abosoort}'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result);
			return $record['tekst'];
		} else return '';
	}

	# haalt maaltijden op en voegt extra info toe voor op de maaltijdenpagina
	function getMaaltijden($van = 0, $tot = 0, $mootfilter = true, $uid=null) {
		if($uid==null){
			$uid=LoginLid::instance()->getUid();
		}
		if($uid == 'x999'){ $mootfilter = false; }

		$maaltijdenRaw = $this->getMaaltijdenRaw($van,$tot,$mootfilter);

		$maaltijden=array();
		foreach($maaltijdenRaw as $maaltijd){
			$maaltijd['abotekst'] = $this->getAboTekst($maaltijd['abosoort']);

			if($maaltijd['gesloten']=='1'){
				//als de maaltijd al gesloten is, dan uit de maaltijdgesloten-tabel ophalen.
				$sAanmeldingen="SELECT uid, gasten, gasten_opmerking FROM maaltijdgesloten WHERE uid = '".$uid."' AND maalid = ".$maaltijd['id'].";";
				$rAanmeldingen = $this->_db->query($sAanmeldingen);
				if (($rAanmeldingen !== false) and $this->_db->numRows($rAanmeldingen) > 0) {
					$maaltijd['status']='AAN';
				}else{
					$maaltijd['status']='';
				}
				# Gasten ophalen
				$record = $this->_db->next($rAanmeldingen);
				$maaltijd['gasten'] = $record['gasten'];
				$maaltijd['opmerking'] = $record['gasten_opmerking'];
			}else{
				# status: AAN,AF ABO ''
				# 1a. is er een aan of afmelding voor deze maaltijd?
				$sAanmeldingen="SELECT status, gasten, gasten_opmerking FROM maaltijdaanmelding WHERE uid = '".$uid."' AND maalid = ".$maaltijd['id'].";";
				$rAanmeldingen = $this->_db->query($sAanmeldingen);
				if (($rAanmeldingen !== false) and $this->_db->numRows($rAanmeldingen) > 0) {
					$record = $this->_db->next($rAanmeldingen);
					$maaltijd['status'] = $record['status'];
					# Gasten ophalen
					$maaltijd['gasten'] = $record['gasten'];
					$maaltijd['opmerking'] = $record['gasten_opmerking'];
				} else {
					# 1b. zo nee, is er een abo actief?
					$sAbo="SELECT uid FROM maaltijdabo WHERE uid = '".$uid."' AND abosoort = '".$maaltijd['abosoort']."'";
					$rAbo = $this->_db->query($sAbo);
					if(($rAbo !== false) and $this->_db->numRows($rAbo) > 0) {
						$record = $this->_db->next($rAbo);
						$maaltijd['status'] = 'ABO';
					}else{
						# 1c. zo ook nee, dan status = ''
						$maaltijd['status'] = '';
					}
				}
			}

			# 2. actie is afhankelijk van status en evt. gesloten zijn van de maaltijd
			# actie: AAN, AF, ''
			if(($maaltijd['status']=='AAN' OR $maaltijd['status']=='ABO') AND $maaltijd['gesloten']=='0' ){
				$maaltijd['actie'] = 'af';
			}elseif(($maaltijd['status']=='AF' OR $maaltijd['status']=='') AND $maaltijd['aantal'] != $maaltijd['max'] AND $maaltijd['gesloten'] == '0' ){
				$maaltijd['actie'] = 'aan';
			}else{
				$maaltijd['actie'] = '';
			}
			$maaltijden[]=$maaltijd;
		}
		return $maaltijden;

	}

	# kijkt of er een maaltijd bestaat met deze maalid
	# te gebruiken alvorens een object maaltijd aan te maken
	function isMaaltijd($maalid) {
		if (!is_numeric($maalid)) {
			$this->_error = "De opgegeven maaltijd bestaat niet.";
			return false;
		}
		$result = $this->_db->select("SELECT * from `maaltijd` WHERE `id` = '$maalid'");
		if (($result === false) or $this->_db->numRows($result) == 0) {
			$this->_error = "De opgegeven maaltijd bestaat niet.";
			return false;
		}
		return true;
	}

	# wrapper-functie voor aanmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function aanmelden($maalid, $uid = '') {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->aanmelden($uid)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# wrapper-functie voor afmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function afmelden($maalid, $uid = '') {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->afmelden($uid)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# wrapper-functie voor gasten aanmelden, die controleert of de maaltijd wel bestaat
	# en om te zorgen dat foutmeldingen goed terugkomen in de pagina
	function gastenAanmelden($maalid, $gasten, $opmerking) {
		# isMaaltijd zet zelf een error als het nodig is
		if (!$this->isMaaltijd($maalid)) return false;
		$maaltijd = new Maaltijd($maalid);
		if (!$maaltijd->gastAanmelden($gasten, $opmerking)) {
			$this->_error = $maaltijd->getError();
			$this->_proxyerror = $maaltijd->getProxyError();
			return false;
		}
		return true;
	}

	# abo aanzetten voor huidige gebruiker
	function addAbo($abosoort) {
		# Kijk of deze abosoort geldig is voor deze persoon, en of we m aan kunnen zetten
		$geenabo = $this->getNotAboSoort();
		if (!array_key_exists($abosoort, $geenabo)) {
			$this->_error = "Er is een ongeldige abonnementsvorm opgegeven, of dit abo is al ingeschakeld voor u.";
			return false;
		}

		# abo toevoegen
		$uid = LoginLid::instance()->getUid();
		$result = $this->_db->query("INSERT INTO `maaltijdabo` (`uid`,`abosoort`) VALUES ('{$uid}','{$abosoort}')");

		# kijken of er maaltijden zijn in de toekomst met dit abo die VOL zijn, en daar AFmeldingen voor maken
		# bij de andere maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id']);
				if ($record['aantal'] == $record['max']) $maaltijd->afmelden();
				else $maaltijd->recount();
				unset($maaltijd);
			}
		}

		return true;
	}

	# abo uitzetten voor huidige gebruiker
	function delAbo($abosoort) {
		# kijk of $abosoort voorkomt in de abo's van deze persoon
		$abos = $this->getAbo();
		if (!array_key_exists($abosoort, $abos)) {
			$this->_error = "Er is een ongeldige abonnementsvorm opgegeven, of dit abo is niet ingeschakeld voor u.";
			return false;
		}

		# abo verwijderen
		$uid = LoginLid::instance()->getUid();
		$result = $this->_db->query("DELETE FROM `maaltijdabo` WHERE `uid` = '{$uid}' AND `abosoort` = '{$abosoort}'");

		# bij de maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id']);
				# als gebruiker geen expliciete AAN of AF heeft kan het aantal inschrijvingen
				# veranderen doordat zijn abo's veranderen
				if ($maaltijd->getStatus() == 'AUTO') $maaltijd->recount();
				unset($maaltijd);
			}
		}
		return true;
	}

	# abo's opvragen voor huidige gebruiker
	public function getAbo($uid=null) {
		$abos = array();
		if($uid==null){
			$uid = LoginLid::instance()->getUid();
		}
		$qAbo="
			SELECT maaltijdabosoort.abosoort, maaltijdabosoort.tekst
			FROM maaltijdabo, maaltijdabosoort
			WHERE maaltijdabo.abosoort = maaltijdabosoort.abosoort
				AND maaltijdabo.uid = '".$uid."';";
		$rAbo=$this->_db->query($qAbo);
		if (($rAbo !== false) and $this->_db->numRows($rAbo) > 0) {
			while ($record = $this->_db->next($rAbo)){
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}
	# alle abo's opvragen, ook het 'Geen' abo...
	# deze functie wordt gebruikt om het soort abo te kunnen kiezen bij maaltijdenbeheer
	function getAbos() {
		$abos = array();
		$result = $this->_db->select("
			SELECT
				maaltijdabosoort.abosoort AS abosoort,
				maaltijdabosoort.tekst AS tekst
			FROM
				maaltijdabosoort;");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}
	# Controleer of het gegeven abonnement wel bestaat.
	function isValidAbo($abo){ return $abo != 'A_GEEN' and array_key_exists($abo, $this->getAbos()); }

	# alle abosoorten opvragen, als deze gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	# het 'Geen' abonnement wordt hier uitgefilterd
	function getAboSoort($mootfilter = true) {
		$abos = array();
		if ($mootfilter === true) $moot = LoginLid::instance()->getLid()->getMoot();
		$result = $this->_db->select("SELECT * FROM maaltijdabosoort WHERE NOT abosoort='A_GEEN'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				if ($mootfilter === true and preg_match("/MOOT[^{$moot}]{1}/", $record['abosoort'])) continue;
				if ($mootfilter === true and preg_match("/UBER[^{$moot}]{1}/", $record['abosoort'])) continue;
				$abos[$record['abosoort']] = $record['tekst'];
			}
		}
		return $abos;
	}

	# alle abosoorten die de ingelogde gebruiker *niet* heeft aanstaan
	function getNotAboSoort($mootfilter = true) {
		$abos = $this->getAbo();
		$abosoorten = $this->getAboSoort($mootfilter);
		return array_diff_key($abosoorten, $abos);
	}

	# array van uids/namen maken die (behalve zichzelf) door de ingelogde persoon zijn aangemeld voor deze maaltijd
	function getProxyAanmeldingen($uid, $maalid) {
		$wienogmeer = array();
		$result = $this->_db->select("
			SELECT uid
			FROM maaltijdaanmelding
			WHERE maalid = {$maalid} AND door = '{$uid}' AND uid <> door AND status = 'AAN'
		");
		if (($result !== false) and $this->_db->numRows($result) > 0)
			while ($record = $this->_db->next($result)) $wienogmeer[$record['uid']] = (string)LidCache::getLid($record['uid']);
		return $wienogmeer;
	}

	function getAboCount ($abosoort) {
		$abosoort = $this->_db->escape($abosoort);
		$result = $this->_db->select("SELECT uid FROM maaltijdabo WHERE abosoort = '{$abosoort}'");
		if ($result !== false) return $this->_db->numRows($result);
		return 0;
	}

}

?>
