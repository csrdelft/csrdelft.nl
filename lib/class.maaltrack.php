<?php
#
# Civitas Studiosorum Reformatorum
# (c)2005 Hans van Kranenburg

#
# Maaltrack
# /lib/class.maaltrack.php
#

# MaalTrack bevat overkoepelende functies voor maaltijden
# - maaltijd aanmaken
# - maaltijd verwijderen
# - maaltijdenlijst opvragen
# - abonnementen aan/uitzetten

require_once("class.maaltijd.php");

class MaalTrack {
	# MySQL connectie
	var $_db;
	# Ingelogde persoon
	var $_lid;
	# evt. foutboodschap
	var $_error;
	var $_proxyerror;

	function MaalTrack(&$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	
	function getError() { $error = $this->_error; $this->_error = ""; return $error; }
	function getProxyError() { $proxyerror = $this->_proxyerror; $this->_proxyerror = ""; return $proxyerror; }
	
	# datum - timestamp wanneer de maaltijd is
	# tekst - omschrijving/menu van de maaltijd
	# abosoort - enum-waarde van een abo dat geldt voor deze maaltijd
	# tp = tafelpraeses-uid
	# max - maximaal aantal inschrijvingen
	function addMaaltijd($datum, $tekst, $abosoort, $tp, $max = MAX_MAALTIJD) {
		$datum = (int)$datum;
		$max = (int)$max;
		
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
		$tekst = mb_substr($tekst,0,200);
		$tekst = $this->_db->escape($tekst);
				
		# kijk of $tp voorkomt in de ledenlijst
		if ($tp != "") {
			$tp = $this->_db->escape($tp);
			$result = $this->_db->select("SELECT * from `lid` WHERE `uid` = '$tp'");	
			if (($result === false) or $this->_db->numRows($result) == 0) {
				$this->_error = "De tafelpraeses moet voorkomen in de ledenlijst."; 
				return false;
			}
		}
		
		# kijk of $abosoort voorkomt in tabel maaltijdabosoort
		if ($abosoort != "") {
			$abosoort = $this->_db->escape($abosoort);
			$result = $this->_db->select("SELECT * from `maaltijdabosoort` WHERE `abosoort` = '$abosoort'");	
			if (($result === false) or $this->_db->numRows($result) == 0) {
				$this->_error = "Er is geen bestaande abonnementsvorm opgegeven."; 
				return false;
			}
		}
				
		# controleer of het maximum aantal > 0 en <= MAX_MAALTIJD is
		if ($max <= 0 or $max > MAX_MAALTIJD) {
			$this->_error = "Het maximaal aantal eters moet tussen 1 en " . MAX_MAALTIJD . " zijn."; 
			return false;
		}
		
		# voeg de maaltijd toe en geef het maalid terug, of false als het
		# niet gelukt is.
		if (!$this->_db->query("
			INSERT INTO maaltijd (datum,tekst,abosoort,max,tp)
			VALUES ('$datum','$tekst','$abosoort','$max','$tp')
		")) return false;
		return $this->_db->insert_id();
	}
	
	function removeMaaltijd($maalid) {
		if (!is_numeric($maalid)) {
			$this->_error = "Gebruik een numerieke maaltijd-id waarde om te verwijderen";
			return false;			
		}
		# kijk of de maaltijd wel bestaat.
		$result = $this->_db->select("SELECT * from `maaltijd` WHERE `id` = '{$maalid}'");	
		if (($result === false) or $this->_db->numRows($result) == 0) {
			$this->_error = "Deze maaltijd bestaat niet";
			return false;			
		}
		
		# verwijder alle aan/afmeldingen voor deze maaltijd
		# ...van leden
		$this->_db->query("DELETE FROM `maaltijdaanmelding` WHERE `maalid` = '{$maalid}'");

		# ...van gasten
		#FIXME

		# verwijder de maaltijd zelf
		$this->_db->query("DELETE FROM `maaltijd` WHERE `id` = '{$maalid}'");
		
		return true;		
	}
	
	# haalt maaltijden uit de maaltijdentabel op, voor uitgebreidere info
	# voor in de kolommen op de maaltijdencontent pagina, zie getMaaltijden hieronder
	# als de gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	function getMaaltijdenRaw($van = 0, $tot = 0, $mootfilter = true) {
		# kijk in db en haal alle maaltijden op waarbij de begintijd
		# na $van is, en voor $tot

		# meestal zal voor $van time() gebruikt worden, als er niets is opgegeven
		# dan wordt ook de huidige tijd gebruikt
		if ($van == 0) $van = time();
		
		# als $tot niet is opgegeven, of 0 is, dan worden alle maaltijden vanaf
		# van teruggegeven, gesorteerd op tijd
		$tot = (int)$tot;
		$totsql = ($tot != 0) ? " AND `datum` < '{$tot}'" : "";
		
		# mootfilter
		if ($mootfilter === true) $moot = $this->_lid->getMoot();
		
		$maaltijden = array();
		$result = $this->_db->select("SELECT * from `maaltijd` WHERE `datum` > '{$van}'{$totsql} ORDER BY datum ASC");	
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				if ($mootfilter === true and preg_match("/MOOT[^{$moot}]{1}/", $record['abosoort'])) continue;
				$maaltijden[] = $record;
			}
		}
		# id, datum, gesloten, tekst, abosoort, max, aantal, tp
		return $maaltijden;
	}
	
	# haalt maaltijden op en voegt extra info toe voor op de maaltijdenpagina
	function getMaaltijden($van = 0, $tot = 0, $mootfilter = true) {
		$uid = $this->_lid->getUid();
		if ($uid == 'x999') $mootfilter = false;
		$maaltijden = $this->getMaaltijdenRaw($van,$tot,$mootfilter);
		#print_r($maaltijden);
		
		$maalxtra = array();
		
		foreach ($maaltijden as $maaltijd) {
			$xtra[] = array();
			# id: maalid
			$xtra['id'] = $maaltijd['id'];
			# datum: wanneer is de maaltijd
			$xtra['datum'] = $maaltijd['datum'];
			# gesloten: is de inschrijving gesloten?
			$xtra['gesloten'] = $maaltijd['gesloten'];
			# tekst: menu/omschrijving
			$xtra['tekst'] = $maaltijd['tekst'];
			# aantal: aantal inschrijvingen
			$xtra['aantal'] = $maaltijd['aantal'];
			# max: max aantal inschrijvingen
			$xtra['max'] = $maaltijd['max'];
			
			# abotekst: tekst van het abo dat van toepassing is of ''
			$result = $this->_db->select("SELECT tekst FROM maaltijdabosoort WHERE abosoort = '{$maaltijd['abosoort']}'");
			if (($result !== false) and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				$xtra['abotekst'] = $record['tekst'];
			} else $xtra['abotekst'] = '';
			
			# tp: civitasnaam van de tp
			# FIXME
			
			# status: AAN,AF ABO ''
			# 1a. is er een aan of afmelding voor deze maaltijd?
			$result = $this->_db->select("SELECT * FROM maaltijdaanmelding WHERE uid = '{$uid}' AND maalid = '{$maaltijd['id']}'");
			if (($result !== false) and $this->_db->numRows($result) > 0) {
				$record = $this->_db->next($result);
				$xtra['status'] = $record['status'];
			} else {
				# 1b. zo nee, is er een abo actief?
				$result = $this->_db->select("SELECT * FROM maaltijdabo WHERE uid = '{$uid}' AND abosoort = '{$maaltijd['abosoort']}'");
				if (($result !== false) and $this->_db->numRows($result) > 0) {
					$record = $this->_db->next($result);
					$xtra['status'] = 'ABO';
				# 1c. zo ook nee, dan status = ''
				} else $xtra['status'] = '';
			}			
			
			# 2. actie is afhankelijk van status en evt. gesloten zijn van de maaltijd
			# actie: AAN,AF,''
			if (($xtra['status'] == 'AAN' or $xtra['status'] == 'ABO') and $xtra['gesloten'] == '0' ) $xtra['actie'] = 'af';
			elseif (($xtra['status'] == 'AF' or $xtra['status'] == '')
			        and $maaltijd['aantal'] != $maaltijd['max'] and $xtra['gesloten'] == '0' ) 
				$xtra['actie'] = 'aan';
			else $xtra['actie'] = '';
		
			$maalxtra[] = $xtra;
		}
		
		return $maalxtra;
	
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
		$maaltijd = new Maaltijd($maalid, $this->_lid, $this->_db);
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
		$maaltijd = new Maaltijd($maalid, $this->_lid, $this->_db);
		if (!$maaltijd->afmelden($uid)) {
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
		$uid = $this->_lid->getUid();
		$result = $this->_db->query("INSERT INTO `maaltijdabo` (`uid`,`abosoort`) VALUES ('{$uid}','{$abosoort}')");

		# kijken of er maaltijden zijn in de toekomst met dit abo die VOL zijn, en daar AFmeldingen voor maken
		# bij de andere maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id'], $this->_lid, $this->_db);
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
		$uid = $this->_lid->getUid();
		$result = $this->_db->query("DELETE FROM `maaltijdabo` WHERE `uid` = '{$uid}' AND `abosoort` = '{$abosoort}'");

		# bij de maaltijden met dit abo een recount doen
		$van = time();
		$result = $this->_db->query("SELECT * FROM maaltijd WHERE abosoort = '{$abosoort}' AND gesloten = '0'");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$maaltijd = new Maaltijd ($record['id'], $this->_lid, $this->_db);
				# als gebruiker geen expliciete AAN of AF heeft kan het aantal inschrijvingen
				# veranderen doordat zijn abo's veranderen
				if ($maaltijd->getStatus() == 'AUTO') $maaltijd->recount();
				unset($maaltijd);
			}
		}		
		return true;
	}
	
	# abo's opvragen voor huidige gebruiker
	function getAbo() {
		$abos = array();
		$uid = $this->_lid->getUid();
		$result = $this->_db->select("
			SELECT maaltijdabosoort.abosoort,maaltijdabosoort.tekst
			FROM maaltijdabo,maaltijdabosoort
			WHERE maaltijdabo.abosoort = maaltijdabosoort.abosoort
				AND maaltijdabo.uid = '{$uid}'
		");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) { $abos[$record['abosoort']] = $record['tekst']; }
		}
		return $abos;
	}


	function hasAbo($abosoort) {
		if ($abosoort != '') {
			$abosoort = $this->_db->escape($abosoort);
			$result = $this->_db->select("SELECT * FROM maaltijdabo WHERE abosoort = '{$abosoort}'");
			if (($result !== false) and $this->_db->numRows($result) > 0) return true;
		}
		return false;
	}

	# alle abosoorten opvragen, als deze gebruiker uit moot 1-4 is, hou daar dan rekening mee
	# deze functionaliteit kan uitgezet worden door $mootfilter = false te zetten als argument
	function getAboSoort($mootfilter = true) {
		$abos = array();
		if ($mootfilter === true) $moot = $this->_lid->getMoot();
		$result = $this->_db->select("SELECT * FROM `maaltijdabosoort`");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				if ($mootfilter === true and preg_match("/MOOT[^{$moot}]{1}/", $record['abosoort'])) continue;
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
			while ($record = $this->_db->next($result)) $wienogmeer[$record['uid']] = $this->_lid->getFullName($record['uid']);
		return $wienogmeer;			
	}

}

?>
