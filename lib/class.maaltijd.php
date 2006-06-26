<?php
#
# Civitas Studiosorum Reformatorum
# PubCie 04/05
# (c)2004 C.S.R. Delft
#
# http://www.csrdelft.nl
# pubcie@csrdelft.nl
#

#
# Maaltijd
# /lib/class.maaltijd.php
#

# De logica van Maaltijd zit als volgt in elkaar:
#
# Er zijn maaltijden met een aantal eigenschappen, leden kunnen abonnementen
# hebben, of zich los inschrijven voor maaltijden.
#
# Qua inschrijving van een lid voor een bepaalde maaltijd zijn er 3 mogelijkheden:
# AUTO inschrijving klappert mee met een abo
# AAN - expliciet aanmelden
# AF - expliciet afmelden
#
# de handmatige opties overrulen een abonnement.
# zodra het maximum van een maaltijd is bereikt, kan er niet meer voor ingeschreven
# worden. als er een abo wordt aangezet op een volle maaltijd zal dit resulteren in
# een expliciete uitschrijving voor de reeds volle maaltijd die het abo overschrijft
# deze expliciete uitschrijving kan niet veranderd worden in auto of aan als de
# maaltijd vol is, en het abo nog aanstaat
#
# N.B. Het controleren op permissies en het controleren van correctheid van
# opgestuurde data in het formulier gebeurt *niet* hier, maar een stap eerder,
# bij het inladen van de FORM-data.
#

class MaalTijd {
	# MySQL connectie
	var $_db;
	# lid-object van ingelogde gebruiker
	var $_lid;
	# id van de maaltijd waar we bewerkingen op uitvoeren
	var $_maalid;
	# Evt. foutmelding
	var $_error = '';
	var $_proxyerror = '';
	
	# maaltijd-record
	var $_maaltijd = false;

	# we gaan bewerkingen uitvoeren op een maaltijd, onder verantwoordelijkheid van een bepaald lid
	# NB!! Gebruik MaalTrack::isMaaltijd voor controle of de maaltijd wel bestaat
	function MaalTijd($maalid, $lid, $db) {
		$this->_maalid = (int)$maalid;
		$this->_lid =& $lid;
		$this->_db =& $db;
		
		# gegevens van de maaltijd inladen
		$result = $this->_db->select("SELECT * FROM `maaltijd` WHERE `id`='{$this->_maalid}'");
		if (($result !== false) and $this->_db->numRows($result) > 0)
			$this->_maaltijd = $this->_db->next($result);
		
	}

	function getError() {
		$error = $this->_error;
		$this->_error = "";
		return $error;
	}	
	
	# De 'proxy' is het aan/afmelden van anderen via je eigen login
	function getProxyError() {
		$error = $this->_proxyerror;
		$this->_proxyerror = "";
		return $error;
	}
	
	# wat gegevens voor de maaltijdprintlijst
	function getDatum() { return $this->_maaltijd['datum']; }
	function getTP() { return $this->_maaltijd['tp']; }
	function getMaalId() { return $this->_maalid; }

	# Aanmelden van een gebruiker voor deze maaltijd.
	function aanmelden($uid = '') {
		if ($uid == '') $uid = $this->_lid->getUid();
		$proxy = ($uid != $this->_lid->getUid()) ? true : false;

		# kijken of er wel een geldige uid is opgegeven
		if ($proxy and (!$this->_lid->uidExists($uid) or !preg_match('/S_(GAST)?LID/', $this->_lid->getLidStatus($uid))) ) {
			$this->_proxyerror = "Opgegeven lid bestaat niet of is geen gewoon Lid.";
			return false;
		}

		# kijken of iemand anders aangemeld wordt voor een maaltijd	die meer dan
		# MAALTIJD_PROXY_MAX_TOT vooruit is
		if ($proxy and ($this->_maaltijd['datum'] - time()) > MAALTIJD_PROXY_MAX_TOT) {
			$this->_proxyerror = "U kunt een ander persoon nu niet voor deze maaltijd opgeven.";
			return false;
		}
		
		if ($proxy) $fullname = $this->_lid->getFullname($uid);

		# kan er ueberhaupt nog veranderd worden aan deze maaltijd?
		if ($this->_maaltijd['gesloten'] == '1') {
			if (!$proxy) $this->_error = "De inschrijving voor deze maaltijd is inmiddels gesloten.";
			else $this->_proxyerror = "De inschrijving voor deze maaltijd is inmiddels gesloten.";
			return false;
		}
	
		# kijk of deze gebruiker al was aan- of afgemeld
		$status = $this->getStatus($uid);
		# $status is nu 'AAN', 'AF' of 'AUTO'
		
		# combineer de gegevens en kijk of de gewenste actie in een
		# netto extra inschrijving resulteert, en of dat kan.
		# extra inschrijving als:
		# - status AF
		# - status AUTO en geen abo
		if (($status == 'AF' or ($status == 'AUTO' and !$this->heeftAbo($uid))) and $this->isVol()) {
			if (!$proxy) $this->_error = "De aanmelding is mislukt omdat het maximaal aantal inschrijvingen inmiddels is bereikt.";
			else $this->_proxyerror = "De aanmelding is mislukt omdat het maximaal aantal inschrijvingen inmiddels is bereikt.";
			return false;
		}
		
		# aanmelding wegschrijven
		$time = time();
		$door = $this->_lid->getUid();
		if (isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
		else $ip = '0.0.0.0';

		# als er een AF stond, maken we er een AAN van
		if ($status == 'AF') {
			$this->_db->query("
				UPDATE `maaltijdaanmelding`
				SET
					`status` = 'AAN',
					`time` = {$time},
					`door` = '{$door}',
					`ip` = '{$ip}'
				WHERE `maalid`='{$this->_maalid}' AND `uid` = '{$uid}'
			");
			$this->recount();
			if ($proxy) $this->_proxyerror = "{$fullname} is nu aangemeld voor de maaltijd.";
			return true;
		# als er nog niets stond zetten we een AAN in de tabel
		} elseif ($status == 'AUTO') {
			$this->_db->query("
				INSERT INTO `maaltijdaanmelding`
				(`uid`, `maalid`, `status`, `time`, `door`, `ip`)
				VALUES('{$uid}', '{$this->_maalid}', 'AAN', '{$time}', '{$door}', '{$ip}');
			");
			$this->recount();
			return true;
		# als gebruiker al is aangemeld zeggen dat dat al zo is
		} elseif ($status == 'AAN') {
			if (!$proxy) $this->_error = "U bent al aangemeld voor deze maaltijd.";
			else $this->_proxyerror = "De persoon die u wilt aanmelden is inmiddels al aangemeld voor deze maaltijd.";
			return false;
		}
		return false;
	}
	
	# Afmelden van een gebruiker voor deze maaltijd.
	function afmelden($uid = '') {
		if ($uid == '') $uid = $this->_lid->getUid();
		$proxy = ($uid != $this->_lid->getUid()) ? true : false;

		if ($proxy) {
			# afmelden anderen mag als we MAAL_MOD rechten hebben
			if ($this->_lid->hasPermission('P_MAAL_MOD')) {
			# of als we MAAL_WIJ hebben en op confide zijn 
			} elseif (opConfide() or $this->aangemeldDoor($uid, $this->_lid->getUid())) {
			} else {
				$this->_proxyerror = "U heeft geen rechten om personen af te melden die u niet zelf aangemeld heeft.";
				return false;
			}
		}

		if ($proxy and !$this->_lid->uidExists($uid)) {
			$this->_proxyerror = "Opgegeven lid bestaat niet.";
			return false;
		}
		if ($proxy) $fullname = $this->_lid->getFullname($uid);

		# kan er ueberhaupt nog veranderd worden aan deze maaltijd?
		if ($this->_maaltijd['gesloten'] == '1') {
			if (!$proxy) $this->_error = "De inschrijving voor deze maaltijd is inmiddels gesloten.";
			else $this->_proxyerror = "De inschrijving voor deze maaltijd is inmiddels gesloten.";
			return false;
		}

		# kijk of deze gebruiker al was aan- of afgemeld
		$status = $this->getStatus($uid); //echo $status;
		# $status is nu 'AAN', 'AF' of 'AUTO'

		# afmelden zal geen extra inschrijving opleveren, dus we letten niet op isvol();

		# afmelding wegschrijven
		$time = time();
		$door = $this->_lid->getUid();
		if (isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
		else $ip = '0.0.0.0';

		# als er een AAN stond, maken we er een AF van
		if ($status == 'AAN') {
			$this->_db->query("
				UPDATE `maaltijdaanmelding`
				SET
					`status` = 'AF',
					`time` = {$time},
					`door` = '{$door}',
					`ip` = '{$ip}'
				WHERE `maalid`='{$this->_maalid}' AND `uid` = '{$uid}'
			");
			$this->recount();
			return true;
		# als er niets stond een AF neerzetten
		} elseif ($status == 'AUTO') {
			$this->_db->query("
				INSERT INTO `maaltijdaanmelding`
				(`uid`, `maalid`, `status`, `time`, `door`, `ip`)
				VALUES('{$uid}', '{$this->_maalid}', 'AF', '{$time}', '{$door}', '{$ip}');
			");
			$this->recount();
			return true;
		# als er al was afgemeld niets doen
		} elseif ($status == 'AF') {
			if (!$proxy) $this->_error = "U bent al afgemeld voor deze maaltijd.";
			else $this->_proxyerror = "{$fullname} al afgemeld voor deze maaltijd.";
			return false;
		}
		return false;
	}

	/*
	function automatisch($uid = '') {
		if ($uid == '') $uid = $this->_lid->getUid();
		if (!$this->_lid->uidExists($uid)) {
			$this->_error = "Opgegeven lid bestaat niet.";
			return false;
		}

		# kan er ueberhaupt nog veranderd worden aan deze maaltijd?
		if ($this->_maaltijd['e()) {
			$this->_error = "De inschrijving voor deze maaltijd is inmiddels gesloten.";
			return false;
		}

		# kijk of deze gebruiker al was aan- of afgemeld
		$status = $this->getStatus($uid);
		# $status is nu 'AAN', 'AF' of 'AUTO'
		
		# kijk of er een abo is
		$abo = $this->heeftAbo($uid);

		# combineer de gegevens en kijk of de gewenste actie in een
		# netto extra inschrijving resulteert, en of dat kan.
		# extra inschrijving als:
		# - status AF en abo
		if ($status == 'AF' and $abo and $this->_isVol()) {
			$this->_error = "U kunt niet automatisch aangemeld worden omdat het maximaal aantal inschrijvingen inmiddels is bereikt.";
			return false;
		}
		
		# als er AAN of AF stond, dan verwijderen we die
		if ($status == 'AAN' or $status == 'AF') {
			# probeer een aan- of afmelding weg te gooien, er wordt niet echt naar fout/succes gekeken...
			$this->_db->query("DELETE FROM `maaltijdaanmelding` WHERE `maalid`='{$this->_maalid}' AND `uid` = {$uid}");
			$this->recount();
			return true;
		# als er niets stond zo laten
		} elseif ($status == 'AUTO') {
			return true;
		}
		return false;
	}
	*/
		
	function heeftAbo($uid = '') {
		if ($uid == '') $uid = $this->_lid->getUid();
		if (!$this->_lid->uidExists($uid)) {
			$this->_error = "Opgegeven lid bestaat niet.";
			return false;
		}
		# kijk of deze gebruiker een abo voor deze maaltijd heeft
		$result = $this->_db->select("SELECT `id` FROM `maaltijdabo` WHERE `uid` = {$uid} AND `abosoort` = {$this->_maaltijd['abosoort']}");
		if (($result !== false) and $this->_db->numRows($result) > 0) return true;
		return false;
	}

	function getStatus($uid = '') {
		# kijk of deze gebruiker al was aan- of afgemeld
		$result = $this->_db->select("SELECT status FROM maaltijdaanmelding WHERE maalid='{$this->_maalid}' AND uid = '{$uid}'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result); //print_r($record);
			if ($record['status'] == 'AAN' or $record['status'] == 'AF') return $record['status'];
		}
		return 'AUTO';
	}
	
	# is $uid aangemeld door $door?
	function aangemeldDoor($uid, $door) {
		$result = $this->_db->select("
			SELECT *
			FROM maaltijdaanmelding
			WHERE maalid='{$this->_maalid}' AND uid = '{$uid}' AND door = '{$door}' AND status = 'AAN'
		");
		if (($result !== false) and $this->_db->numRows($result) > 0) return true;
		return false;		
	}
	
	function gastAanmelden($naam, $aantal, $opm) {
		$aantal = (int)$aantal;
		$naam = mb_substr($naam,0,100);
		$opm = mb_substr($opm,0,100);
		
	}
	
	# tel het aantal aanmeldingen voor een maaltijd opnieuw en zet het in
	# de tabel bij de maaltijd
	function recount() {
		# tel alle abo's:
		# iedereen die de abosoort als abo heeft, en niet voorkomt in de aanmeldingentabel
		$abo = 0;				
		$result = $this->_db->select("
			SELECT count(*)
				FROM `maaltijdabo`
				WHERE
					`abosoort` = '{$this->_maaltijd['abosoort']}'
						and
					`uid` NOT IN (SELECT `uid` FROM `maaltijdaanmelding` WHERE `maalid` = '{$this->_maalid}')
		");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result);
			$abo = $record['count(*)'];
		}
		
		# tel alle aanmeldingen
		# aantal AAN in de maaltijdaanmeldingtabel
		$aan = 0;
		$result = $this->_db->select("SELECT count(*) FROM `maaltijdaanmelding` WHERE `maalid`='{$this->_maalid}' AND `status` = 'AAN'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$record = $this->_db->next($result);
			$aan = $record['count(*)'];
		}
		
		# tel alle gasten
		# FIXME - er is nog geen gastenaanmelding

		# totaal berekenen en opslaan
		$totaal = $abo + $aan;
		$this->_maaltijd['aantal'] = $totaal;
		$this->_db->query("UPDATE `maaltijd` SET `aantal` = '{$totaal}' WHERE `id` = '{$this->_maalid}'");
		#echo "UPDATE `maaltijd` SET `aantal` = '{$totaal}' WHERE `id` = '{$this->_maalid}'";
		
		return $totaal;
	}
	
	function isVol() {
		return $this->_maaltijd['aantal'] >= $this->_maaltijd['max'];
	}
	
	# deze functie mailt de link met pin van deze maaltijd naar maaltijden@csrdelft.nl
	# als de maaltijd nog niet gesloten was, dan 
	function mailPin() {
#23456789012345678901234567890123456789012345678901234567890123456789012
		$datum = date ('l j F Y', $this->_maaltijd['time']);
		$emailtekst = <<<EOD
Beste koks,

Maaltijd: {$datum}

U kunt op onderstaande link klikken om een lijst op te vragen met
inschrijvingen. Op deze pagina kunt u ook de inschrijving sluiten.

De lijst, die u vervolgens ziet kunt uitprinten
http://www.csrdelft.nl/leden/maaltijdlijst.php?maalid={$this->_maalid}&pin={$this->_maaltijd['pin']}

Groet en kookze,
de MaalCie && PubCie
EOD;
		mail('pubcie@csrdelft.nl', "Maaltijdlijst {$datum}", $emailtekst);
		#mail('maaltijden@csrdelft.nl', "Maaltijdlijst {$datum}", $emailtekst);
	}
	
	function isGesloten() {
		return $this->_maaltijd['gesloten'] == '1';
	}
	function sluit() {
		# inschrijving gesloten?
		if ($this->_maaltijd['gesloten'] == '1') return false;

		# sluit de maaltijd door het vlaggetje gesloten te zetten...
		$this->_maaltijd['gesloten'] = '1';
		#print ("UPDATE maaltijd SET gesloten = '1' WHERE id = {$this->_maalid}\n");
		$this->_db->query("UPDATE maaltijd SET gesloten = '1' WHERE id = {$this->_maalid}");

		# haal de aanmeldingen op en prop ze in de maaltijdgesloten tabel
		# FIXME
		
		# verwijder de losse aanmeldingen uit de maaltijdaanmeldingtabel		
		# FIXME
		
		return true;
	}
	
	# geeft een array terug met de aanmeldingen van leden, (los en abo) door elkaar
	# naast de informatie uit de inschrijvingen tabel staat ook de naam en de opmerking
	# uit het profiel erbij
	
	# N.B. als de maaltijd gesloten is, dan kijken we in de tabel maaltijdgesloten, en nemen daar
	# alles uit wat bij deze maaltijd hoort. Als de maaltijd nog niet is gesloten, is het wat
	# gecompliceerder, en zullen we de gegevens van aan/afmeldingen en abo's moeten combineren.

	# De functie die een maaltijdinschrijving sluit maakt ook gebruik van deze functie om de
	# aanmeldingen over te zetten naar de maaltijdgesloten-tabel. 
	function getAanmeldingen() {
		# inschrijving gesloten?
		//if ($this->_maaltijd['gesloten'] == '1') {
		//	$aan = array();
		//	$result = $this->_db->select("SELECT * FROM maaltijdgesloten WHERE maalid='{$this->_maalid}'");
		//	if (($result !== false) and $this->_db->numRows($result) > 0)
		//		while ($record = $this->_db->next($result)) { $aan[$record['uid']] = $record; }
		//
		//} else {

			# als inschrijving nog niet gesloten is...

			# Eerst opvragen van de losse aanmeldingen AAN
			$aan = array();
			$result = $this->_db->select("SELECT * FROM `maaltijdaanmelding` WHERE `maalid`='{$this->_maalid}' AND `status` = 'AAN'");
			if (($result !== false) and $this->_db->numRows($result) > 0)
				while ($record = $this->_db->next($result)) $aan[$record['uid']] = $record;
		
			# Dan opvragen van de abo's
			$abo = array();
			$result = $this->_db->select("SELECT * FROM `maaltijdabo` WHERE `abosoort`='{$this->_maaltijd['abosoort']}'");
			if (($result !== false) and $this->_db->numRows($result) > 0)
				while ($record = $this->_db->next($result)) $abo[$record['uid']] = $record;
		
			# Dan opvragen van de losse aanmeldingen AF
			$af = array();
			$result = $this->_db->select("SELECT * FROM `maaltijdaanmelding` WHERE `maalid`='{$this->_maalid}' AND `status` = 'AF'");
			if (($result !== false) and $this->_db->numRows($result) > 0)
				while ($record = $this->_db->next($result)) $af[$record['uid']] = $record;
		
			# En die AF en AAN meldingen wegstrepen uit de abolijst.
			$abo = array_diff_key($abo, $af, $aan);
			# vervolgens de overgebleven abo's bij de AAN lijst zetten
			$aan = $aan + $abo;
		
			# en naam en eetwens toevoegen
			$this->_lid->addNames($aan);
		//}

		# nog ff sorteren
		ksort($aan, SORT_NUMERIC);
		
		return $aan;
	}
	
	# geeft een array terug met de gasten-aanmeldingen
	function getAanmeldingenGast() {
		return array();
	}
	
	function getAfTijdelijk() {
		# Dan opvragen van de losse aanmeldingen AF
		$af = array();
		$result = $this->_db->select("SELECT * FROM `maaltijdaanmelding` WHERE `maalid`='{$this->_maalid}' AND `status` = 'AF'");
		if (($result !== false) and $this->_db->numRows($result) > 0)
			while ($record = $this->_db->next($result)) $af[$record['uid']] = $record;
			
		# en naam en eetwens toevoegen
		$this->_lid->addNames($af);
		return $af;	
	
	}

}

?>
