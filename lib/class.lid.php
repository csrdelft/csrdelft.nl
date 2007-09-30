<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once('class.ldap.php');
require_once('class.bestuur.php');

class Lid {
	static private $lid;
	
	### private ###
	protected $_db;
	
	# het profiel van een gebruiker, i.e. zijn regel uit de database die we inladen
	# komt in de sessie...

	# permissies die we gebruiken om te vergelijken met de permissies van
	# een gebruiker. zie functie _loadPermissions()
	protected $_permissions = array();
	protected $_perm_user   = array();

	# Het profiel van de gebruiker... niet meer in de sessie maar bij elke pagina
	# opgehaald, om wijzigingen meteen actief te krijgen.
	protected $_profile;
	
	//singleton functionaliteit...
	private function __construct(){ $this->Lid(); }
	static function get_lid(){
		//als er nog geen instantie gemaakt is, die nu maken
		if(!isset(Lid::$lid)){
			Lid::$lid = new Lid();
		}
		return Lid::$lid;
	}
	function Lid() {
		# we starten op aan het begin van een pagina
		$this->_loadPermissions();
		# database lokaal maken
		$this->_db=Mysql::get_mysql();

		# http://www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
		if (session_id() == 'deleted') session_regenerate_id();

		# kijken in de sessie of er een gebruiker in staat,
		# en of dit een gebruiker is die een profiel in de database heeft.
		# als er een IP-veld in de sessie staat wordt dit vergeleken met het huidige IP
		# waarvan geconnect wordt (kan v4 of v6 zijn)
		if (
			!isset($_SESSION['_uid']) or
			isset($_SESSION['_ip']) and $_SERVER['REMOTE_ADDR'] != $_SESSION['_ip'] or
			!$this->reloadProfile()
		) {
			# zo nee, dan nobody user er in gooien...
			# in dit geval is het de eerste keer dat we een pagina opvragen
			# of er is net uitgelogd waardoor de gegevens zijn leeggegooid
			$this->login('x999','x999',false);
		}
		# experimentele logfunctie
		$this->logBezoek();
	}

	### public ###

	# dispatch the login proces to a separate function based on MODE
	function login($user, $pass = "", $checkip = true) {
		switch (constant('MODE')) {
			case 'CLI':
				return $this->_login_cli($user);
			case 'BOT':
				return $this->_login_bot($user);
			case 'WEB':
			default:
				return $this->_login_web($user, $pass, $checkip);
		}

	}

	# als een gebruiker wordt ingelogd met ipcheck==true, dan wordt het IPv4 adres
	# van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal alleen
	# vanaf dat adres toegang geven tot de website
	function _login_web($user,$pass,$checkip = true) {
		#
		$user = $this->_db->escape($user);

		# eerst proberen we via de user-id de gebruiker te vinden
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$user}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$profile = $this->_db->next($result);
		} else {
			# anders via de nickname N.B. deze nickname search is *case-insensitive*
			$result = $this->_db->select("SELECT * FROM lid WHERE nickname = '{$user}' LIMIT 1");
			if (($result !== false) and $this->_db->numRows($result) > 0) {
				$profile = $this->_db->next($result);
			# anders helaasch 
			} else {
				return false;
			}
		}

		# we hebben nu een gebruiker gevonden en gaan eerst het wachtwoord controleren
		if (!$this->_checkpw($profile['password'], $pass)) return false;

		# als dat klopt laden we het profiel in en richten de sessie in
		$this->_profile = $profile;
		$_SESSION['_uid'] = $profile['uid'];
		
		# sessie koppelen aan ip?
		if ($checkip == true) $_SESSION['_ip'] = $_SERVER['REMOTE_ADDR'];
		else if (isset($_SESSION['_ip'])) unset($_SESSION['_ip']);
		
		return true;
	}

	# login without a password, only for BOT use
	# only uids are supported, no nicknames
	function _login_bot($user) {
		#
		$user = $this->_db->escape($user);

		# search for user uid
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$user}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$this->_profile = $this->_db->next($result);
			return true;
		}
		return false;
	}

	# TODO: implement this
	function _login_cli($user) {
		return false;
	}

	function reloadProfile() {
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$_SESSION['_uid']}' LIMIT 1");
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			$this->_profile = $this->_db->next($result);
			return true;
		}
		return false;
	}	

	function logout() {
		session_unset();
		$this->login('x999','x999',true);
	}

	### public ###

	function hasPermission($descr) {
		# ga alleen verder als er een geldige permissie wordt gevraagd
		if (!array_key_exists($descr, $this->_permissions)) return false;
		# zoek de code op
		$gevraagd = (int) $this->_permissions[$descr];

		# zoek de rechten van de gebruiker op
		$liddescr = $this->_profile['permissies'];
		# ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!array_key_exists($liddescr, $this->_perm_user)) return false;
		# zoek de code op
		$lidheeft = $this->_perm_user[$liddescr];

		# $p is de gevraagde permissie als octaal getal
		# de permissies van de gebruiker kunnen we bij $this->_lid opvragen
		# als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
		# zijn aan de gevraagde permissie. In dat geval bestaat de permissie
		# van het lid dus minimaal uit de gevraagde permissie
		#
		# voorbeeld:
		#  gevraagd:   P_FORUM_MOD: 0000000700
		#  lid heeft:  P_LID      : 0005544500
		#  AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
		#
		#  gevraagd:  P_DOCS_READ : 0000004000
		#  gebr heeft: P_LID      : 0005544500
		#  AND resultaat          : 0000004000 -> ja!

		$resultaat = $gevraagd & $lidheeft;
		if (!($resultaat == $gevraagd)) return false;

		return true;
	}

	function getUid() { return $this->_profile['uid']; }
	function getNickName() { return $this->_profile['nickname']; }
	function getProfile() { return $this->_profile; }
	function getPermissions() { return $this->_profile['permissies']; }
	function getStatus()      { return $this->_profile['status']; }
	function getForumInstelling(){ return array('forum_naam' => $this->_profile['forum_name']); }
	function getForumNaamInstelling(){ return $this->_profile['forum_name']; }
	
	
	function getEmail($uid=''){
		if($uid==''){ 
			$uid=$this->getUid(); 
		}else{
			if(!$this->isValidUid($uid)){
				return false;
			}
		}
		$sEmailQuery="
			SELECT email FROM lid WHERE uid='".$uid."' LIMIT 1;";
		$rEmail=$this->_db->query($sEmailQuery);
		$aEmail=$this->_db->next($rEmail);
		return $aEmail['email'];
	}
	/*
	* Deze functie maakt een link met de naam, als de gebruiker is ingelogged, anders gewoon een naam.
	* Dit om te voorkomen dat er op 100 plekken foute paden staan als dat een keer verandert.
	*
	* argumenten:
	*		$uid 						uid van de benodigde naam
	*		$vorm						Vorm van de naam: ( nick, civitas, streeplijst, full, user)
	*		$link						Er wordt een link naar het profiel gemaakt.
	* 	$bHtmlentities	Naam wordt ge-htmlentities()-ed
	*
	*		return: 				string naam
	*
	*/
	function getNaamLink($uid, $vorm='full', $link=false, $aNaam=false, $bHtmlentities=true){
		//als er geen uid is opgegeven, ook geen link of naam teruggeven.
		if($uid=='' AND !$this->isValidUid($uid)){ return ''; }
		$sNaam='';
		//als er geen array wordt meegegeven, of de array is niet compleet genoeg om een naam te tonen, dan de
		//gegevens ophalen uit de database met het opgegeven uid.
		if(!isset($aNaam['voornaam'], $aNaam['achternaam'], $aNaam['tussenvoegsel'], $aNaam['nickname'], $aNaam['geslacht'], $aNaam['status'], $aNaam['postfix'])){
			//betreft het de huidige gebruiker? dan de array van het profiel raadplegen
			if($uid == $this->_profile['uid']){
				$aNaam=$this->_profile;
			}else{
				$rNaam=$this->_db->select(
					"SELECT 
						nickname, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix 
					FROM lid WHERE uid='".$uid."' LIMIT 1;");
				if($rNaam!==false and $this->_db->numRows($rNaam)==1){
					$aNaam=$this->_db->next($rNaam);
				}else{
					return 'onbekend';
				}
			}
		}
		$sVolledigeNaam=$aNaam['voornaam'].' ';
		if($aNaam['tussenvoegsel']!='') $sVolledigeNaam.=$aNaam['tussenvoegsel'].' ';
		$sVolledigeNaam.=$aNaam['achternaam'];

		//link tonen als dat gevraagd wordt EN als gebruiker is ingelogged.
		if($link AND $this->hasPermission('P_LOGGED_IN')){ 
			$sNaam.='<a href="/intern/profiel/'.$uid.'" title="'.$sVolledigeNaam.'">'; 
		}
		//als $vorm==='user', de instelling uit het profiel gebruiken voor vorm
		if($vorm=='user'){
			$vorm=$this->getForumNaamInstelling();
		}
		//civitas of niksnamen, enkel relevant voor het forum, verder is gewoon voornaam [tussenvoegsel] achternaam
		//nog een optie.
		if($vorm==='nick'){
			if($aNaam['nickname']!=''){
				$sTmpNaam=$aNaam['nickname'];
			}else{
				$sTmpNaam=$sVolledigeNaam;
			}			
		}elseif($vorm==='streeplijst'){ // achternaam, voornaam [tussenvoegsel] voor de streeplijst
			$sTmpNaam=$aNaam['achternaam'].', '.$aNaam['voornaam'];
			if($aNaam['tussenvoegsel'] != '') $sTmpNaam.=' '.$aNaam['tussenvoegsel'];
		}elseif($vorm==='full' OR $aNaam['status']=='S_KRINGEL'){
			$sTmpNaam=$sVolledigeNaam;	
		}elseif($vorm==='civitas'){
			if($aNaam['status']=='S_NOVIET'){
				$sTmpNaam='noviet '.$aNaam['voornaam'];
			}else{
				$sTmpNaam=($aNaam['geslacht']=='v') ? 'Ama. ' : 'Am. ';
				if($aNaam['tussenvoegsel'] != '') $sTmpNaam.=ucfirst($aNaam['tussenvoegsel']).' ';
				$sTmpNaam.=$aNaam['achternaam'];				
				if($aNaam['postfix'] != '') $sTmpNaam.=' '.$aNaam['postfix'];
				if($aNaam['status']=='S_OUDLID') $sTmpNaam.='';
			}
		}else{
			$sTmpNaam='ongeldige vorm';
		}
		if($bHtmlentities){ 
			$sNaam.=mb_htmlentities($sTmpNaam); 
		}else{ 
			$sNaam.=$sTmpNaam; 
		}
		if($link AND $this->hasPermission('P_LOGGED_IN')){ $sNaam.='</a>'; }
		return $sNaam;	
	}	
	
	function getFullName($uid='') {
		if($uid==''){ $uid=$this->getUid(); }
		//geen bijnaam of am./ama., geen link, geen input-array.
		return $this->getNaamLink($uid, 'full', false, false);
	}
	
	function getCivitasName($uid=''){
		if($uid==''){ $uid=$this->getUid(); }
		//geen bijnaam, geen link, geen input-array
		return $this->getNaamLink($uid, 'civitas', false, false);
	}
	
	function getMoot() { return $this->_profile['moot']; }
	
	function _loadPermissions() {
		# Hier staan de permissies die voor enkele onderdelen van
		# de website nodig zijn. Ze worden zowel op de 'echte'
		# website als in het beheergedeelte gebruikt.

		# READ = Rechten om het onderdeel in te zien
		# POST = Rechten om iets toe te voegen
		# MOD  = Moderate rechten, dus verwijderen enzo
		# Let op: de rechten zijn cumulatief en octaal
		
		$this->_permissions = array(
			'P_NOBODY'       => 00000000001,
			'P_LOGGED_IN'    => 00000000003, # Leden-menu, eigen profiel raadplegen
			'P_ADMIN'        => 00000000007, # Admin dingen algemeen...	
			'P_FORUM_READ'   => 00000000400, # Forum lezen
			'P_FORUM_POST'   => 00000000500, # Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD'    => 00000000700, # Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_DOCS_READ'    => 00000004000, # Documenten-rubriek lezen
			'P_DOCS_POST'    => 00000005000, # Documenten verwijderen of erbij plaatsen
			'P_DOCS_MOD'     => 00000007000, # euh?
			'P_PROFIEL_EDIT' => 00000010000, # Eigen gegevens aanpassen
			'P_LEDEN_READ'   => 00000040000, # Gegevens over andere leden raadplegen
			'P_LEDEN_EDIT'   => 00000020000, # Profiel van andere leden wijzigen
			'P_LEDEN_MOD'    => 00070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			'P_AGENDA_READ'  => 00000400000, # Agenda bekijken
			'P_AGENDA_POST'  => 00000500000, # Items toevoegen aan de agenda
			'P_AGENDA_MOD'   => 00000700000, # euh?
			'P_NEWS_POST'    => 00001000000, # Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD'     => 00003000000, # Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_OUDLEDEN_EDIT'=> 00020000000, # Profiel van andere leden wijzigen
			'P_OUDLEDEN_READ'=> 00040000000, # Gegevens over andere leden raadplegen
			'P_OUDLEDEN_MOD' => 00070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			                                 # oudleden-mod is gelijk aan leden-mod
			'P_MAAL_IK'      => 00100000000, # kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_MAAL_WIJ'     => 00500000000, # kan ook anderen aanmelden (niet afmelden!)
			'P_MAAL_MOD'     => 00700000000, # mag maaltijd aan- en afmeldingen voor iedereen wijzigen
			'P_MAIL_POST'    => 02000000000, # mag berichtjes in de pubciemail rossen
			'P_MAIL_COMPOSE' => 04000000000, # mag alle berichtjes in de pubcie-mail bewerken, en volgorde wijzigen
			'P_MAIL_SEND'    => 06000000000, # mag de C.S.R.-mail verzenden
			'P_BIEB_READ'    => 00000000020, # Bibliotheek lezen
			'P_BIEB_EDIT'    => 00000000040, # Bibliotheek wijzigen		
			'P_BIEB_MOD'     => 00000000060, # Bibliotheek zowel wijzigen als lezen	
			# N.B. bij uitbreiding van deze octale getallen met nog een cijfer erbij gaat er iets mis, wat weten we nog niet.
		);

		# Deze waarden worden samengesteld uit bovenstaande permissies en
		# worden in de gebruikersprofielen gebruikt als aanduiding voor
		# welke permissie-groep de gebruiker in zit.

		$p = $this->_permissions;
		$this->_perm_user = array(
			'P_NOBODY'     => $p['P_NOBODY'] | $p['P_FORUM_READ'],
			'P_LID'        => $p['P_LOGGED_IN'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_DOCS_READ'] | $p['P_LEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_AGENDA_POST'] + $p['P_MAAL_WIJ'] + $p['P_MAIL_POST'],
			'P_OUDLID'     => $p['P_LOGGED_IN'] | $p['P_LEDEN_READ'] | $p['P_OUDLEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_FORUM_READ'],
			'P_MODERATOR'  => $p['P_ADMIN'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_AGENDA_MOD'] | $p['P_MAAL_MOD'] | $p['P_MAIL_SEND'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD']
		);
		# extra dingen, waarvoor de array perm_user zelf nodig is
		$this->_perm_user['P_PUBCIE']  = $this->_perm_user['P_MODERATOR'];
		$this->_perm_user['P_MAALCIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];
		$this->_perm_user['P_BESTUUR'] = $this->_perm_user['P_LID'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_READ'] | $p['P_NEWS_MOD'] | $p['P_MAAL_MOD'] | 'P_MAIL_COMPOSE' | $p['P_AGENDA_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'];
		$this->_perm_user['P_VAB']     = $this->_perm_user['P_BESTUUR']  | $p['P_OUDLEDEN_MOD'];
		$this->_perm_user['P_KNORRIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];

	}

	function _makepasswd($pass) {
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass.$salt).$salt);
	}

	function _checkpw($hash, $pass) {
		// Verify SSHA hash
		$ohash = base64_decode(substr($hash, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash == $nhash) return true;
		return false;
	}

	function _isSecure($uid, $nick, $passwd, &$error) {
		# We doen een aantal standaard checks die een foutmelding kunnen produceren...
		$error = "";
	
		$sim_uid = 0; $foo = similar_text($uid,$passwd,$sim_uid);
		$sim_nick = 0; $foo = similar_text($nick,$passwd,$sim_nick);

		# Korter dan 6 of langer dan 16 mag niet...
		if (mb_strlen($passwd) < 6 or mb_strlen($passwd) > 60) {
			$error = "Het wachtwoord moet minimaal 6 en maximaal 60 tekens lang zijn. :-/";
		# is het geldige utf8?
		} elseif (!is_utf8($passwd)) {
			$error = "Het nieuwe wachtwoord bevat ongeldige karakters... :-(";
		} elseif (preg_match('/^[0-9]*$/', $passwd)) {
			$error = "Het nieuwe wachtwoord moet ook letters of leestekens bevatten... :-|";
		//eisen zijn wat zwaar, deze er even uit halen
		//} elseif (preg_match('/^[A-Za-z]*$/', $passwd)) {
		//	$error = "Het nieuwe wachtwoord moet ook een cijfer of leesteken bevatten... :-S";
		} elseif ($uid == $passwd) {
			$error = "Het wachtwoord mag niet gelijk zijn aan je gebruikersnaam! :-@";
		} elseif ($sim_uid > 60) {
			$error = "Het wachtwoord lijkt teveel op je gebruikersnaam ;-]";
		} elseif ($sim_nick > 60) {
			$error = "Het wachtwoord lijkt teveel op je bijnaam ;-]";
		#} elseif () {
		}
		return ($error == "");
	}

	function zoekLeden($zoekterm, $zoekveld, $moot, $sort, $zoekstatus = '') {
		$leden = array();
		$zoekfilter='';
		
		# mysql escape dingesen
		$zoekterm = trim($this->_db->escape($zoekterm));
		$zoekveld = trim($this->_db->escape($zoekveld));
		
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if($zoekveld=='naam' AND !preg_match('/^\d{2}$/', $zoekterm)){
			if(preg_match('/ /', trim($zoekterm))){
				$zoekdelen=explode(' ', $zoekterm);
				$iZoekdelen=count($zoekdelen);
				if($iZoekdelen==2){
					$zoekfilter="( voornaam LIKE '%".$zoekdelen[0]."%' AND achternaam LIKE '%".$zoekdelen[1]."%' ) OR";
					$zoekfilter.="( voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
                                        nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%' )";
				}else{
					$zoekfilter="( voornaam LIKE '%".$zoekdelen[0]."%' AND achternaam LIKE '%".$zoekdelen[$iZoekdelen-1]."%' )";
				}
			}else{
				$zoekfilter="
					voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR 
					nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%'";
			}
		}else{
			if(preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld=='uid' OR $zoekveld=='naam')){
				//zoeken op lichtingen...
				$zoekfilter="SUBSTRING(uid, 1, 2)='".$zoekterm."'";
			}else{
				$zoekfilter="{$zoekveld} LIKE '%{$zoekterm}%'";
			}
		}
		$sort = $this->_db->escape($sort);

		# in welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft
		
		$statusfilter = '';
		# we zoeken in leden als
		# 1. ingelogde persoon dat alleen maar mag of
		# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
		if (
			($this->hasPermission('P_LEDEN_READ') and !$this->hasPermission('P_OUDLEDEN_READ') ) or
			($this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'oudleden')
		   ) {
			$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
		}
		# we zoeken in oudleden als
		# 1. ingelogde persoon dat alleen maar mag of
		# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
		if (
			(!$this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') ) or
			($this->hasPermission('P_LEDEN_READ') and $this->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'leden')
		   ) {
			if ($statusfilter != '') $statusfilter .= " OR ";
			$statusfilter .= "status='S_OUDLID'";
		}
		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		$mootfilter = ($moot != 'alle') ? 'AND moot= '.(int)$moot : '';

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			$sZoeken="
				SELECT
					uid, nickname, voornaam, tussenvoegsel, achternaam, postfix, adres, postcode, woonplaats, land, telefoon,
					mobiel, email, geslacht, voornamen, icq, msn, skype, jid, website, beroep, studie, studiejaar, lidjaar, 
					gebdatum, moot, kring, kringleider, motebal, 
					o_adres, o_postcode, o_woonplaats, o_land, o_telefoon, 
					kerk, muziek, eetwens
				FROM 
					lid 
				WHERE 
					(".$zoekfilter.")
				AND 
					($statusfilter) 
				{$mootfilter}
				ORDER BY 
					{$sort}";
			$result = $this->_db->select($sZoeken);
			if ($result !== false and $this->_db->numRows($result) > 0) {
				while ($lid = $this->_db->next($result)) $leden[] = $lid;
			}
		}

		return $leden;
	}
	
	function nickExists($nick) {
		# mysql escape dingesen
		$nick = $this->_db->escape($nick);
		$result = $this->_db->select("SELECT * FROM lid WHERE nickname = '".$nick."'");
		return ($result !== false and $this->_db->numRows($result) > 0);
	}
	
	function isValidUid($uid) {
		return preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}

	function uidExists($uid) {
		if (!$this->isValidUid($uid)) return false;
		
		$result = $this->_db->select("SELECT * FROM lid WHERE uid = '{$uid}'");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			#echo $this->_db->numRows($result);
			return true;
		}
		return false;
	}
	//een methode om te checken of het huidige dan wel het opgegeven lid in het bestuur zit
	function isBestuur($uid=''){
		if($uid==''){ $uid=$this->getUid(); }
		$bestuur=new Bestuur();
		return $bestuur->isBestuur($uid);
	}
	function getLidStatus($uid) {
		# is het wel een geldig lid-nummer?
		if (!$this->isValidUid($uid)) return false;
		
		# opzoeken status
		$uid = $this->_db->escape($uid);
		$result = $this->_db->select("SELECT status FROM lid WHERE uid = '{$uid}'");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			$record = mysql_fetch_assoc($result);
			return $record['status'];
		}	
		return false;
	}

	function getAlleLeden($sort) {
		$leden = array();

		# mysql escape dingesen
		$sort = $this->_db->escape($sort);

		$result = $this->_db->select("
			SELECT * 
			FROM 
				lid 
			WHERE ( 
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL' ) ORDER BY {$sort}");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) $leden[] = $lid;
		}

		return $leden;
	}

	function getVerjaardagen($maand, $dag=0) {
		$maand = (int)$maand; $dag = (int)$dag; $verjaardagen = array();
		$query="
			SELECT 
				uid, voornaam, tussenvoegsel, achternaam, geslacht, email, 
				EXTRACT( DAY FROM gebdatum) as gebdag
			FROM 
				lid 
			WHERE 
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') 
			AND 
				EXTRACT( MONTH FROM gebdatum)= '{$maand}'";
		if($dag!=0)	$query.=" AND gebdag=".$dag;
		$query.=" ORDER BY gebdag;";
		$result = $this->_db->select($query);
		
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while($verjaardag=$this->_db->next($result)){
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;
	}
	
	function getKomende10Verjaardagen() {
		$query="
			SELECT
				uid, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix,
				TO_DAYS(
						CONCAT(
						IF(
							DATE_FORMAT(gebdatum, '%m-%d') < DATE_FORMAT(NOW(), '%m-%d'),
							YEAR(NOW()) + 1,
							YEAR(NOW())
						),
						DATE_FORMAT(gebdatum, '-%m-%d')
					)
				) - TO_DAYS(now()) AS jarig_over,
				YEAR(NOW()) - YEAR(gebdatum) AS leeftijd
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') 
			AND
				NOT gebdatum = '0000-00-00'
			ORDER BY jarig_over ASC, lidjaar, gebdatum, achternaam
			LIMIT 10";
		
		$result = $this->_db->select($query);
		
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while($verjaardag=$this->_db->next($result)){
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;		
	}

	function getMaxKringen($moot=0) {
		$maxkringen = 0;
		$sMaxKringen="
			SELECT 
				MAX(kring) as max 
			FROM 
				lid 
			WHERE 
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') ";
		if($moot!=0){ $sMaxKringen.="AND moot=".$moot; }
		$sMaxKringen.="	LIMIT 1;";
		
		$result = $this->_db->select($sMaxKringen);
		if ($result !== false and $this->_db->numRows($result) > 0) {
			$max = $this->_db->next($result);
			$maxkringen = $max['max'];
			return $maxkringen;
		}else{
			return 0;
		}
	}

	function getMaxMoten() {
		$maxmoten = 0;
		$result = $this->_db->select("
			SELECT MAX(moot) as max FROM lid WHERE (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			$max = $this->_db->next($result);
			$maxmoten = $max['max'];
		}

		return $maxmoten;
	}

	function getKringen() {
		$kring = array();
		$result = $this->_db->select("
			SELECT 
				lid.uid as uid, 
				nickname, 
				voornaam, 
				tussenvoegsel, 
				achternaam, 
				geslacht, 
				postfix, 
				moot, 
				kring, 
				motebal, 
				kringleider,
				email,
				status,
				soccieSaldo				
			FROM 
				lid
			WHERE 
				status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'
			ORDER BY 
				kringleider DESC,
				achternaam ASC;");
		if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($lid = $this->_db->next($result)) {
				$kring[$lid['moot']][$lid['kring']][] = $lid;
			}
		}

		return $kring;
	}
	
	# Deze functie voegt iemand aan een kring toe
	function addUid2kring($uid, $kring, $moot=0){
		//controle op invoer
		//if (!$this->isValidUid($uid)) return false;
		//$kring=(int)$kring; if($kring>10) return false;
		//$moot=(int)$moot; if($moot>4) return false;
		$sKringInvoer="
			UPDATE 
				lid
			SET
				kring=".$kring."";
		if($moot!=0) $sKringInvoer.=", moot=".$moot;
		$sKringInvoer.="			
			WHERE 
				uid='".$uid."'
			LIMIT 1;";
		return $this->_db->query($sKringInvoer);
	}

	function getEetwens(){ return $this->_profile['eetwens']; }
	
	function setEetwens($eetwens){
		$eetwens=trim($this->_db->escape($eetwens));
		//ff streepjes enzo eruit halen, anders komen die op de maaltijdlijst.
		if(strlen($eetwens)<3){ $eetwens=''; }
		$sEetwens="UPDATE lid SET eetwens='".$eetwens."' WHERE uid='".$this->getUid()."';";
		return $this->_db->query($sEetwens);
	}	
	
	function getSaldi($uid='', $alleenRood=false){
		if($uid==''){ $uid=$this->getUid(); }
		$query="
			SELECT
				soccieSaldo, maalcieSaldo
			FROM
				lid
			WHERE
				uid='".$uid."'
			LIMIT 1;";
		$rSaldo=$this->_db->query($query);
		if($rSaldo!==false AND $this->_db->numRows($rSaldo)){
			$aSaldo=$this->_db->next($rSaldo);
			if($alleenRood){
				$return=false;
				if($aSaldo['soccieSaldo']<0){
					$return[]=array('naam' => 'SocCie', 
						'saldo' => sprintf("%01.2f",$aSaldo['soccieSaldo']));
				}
				if($aSaldo['maalcieSaldo']<0){
					$return[]=array('naam' => 'MaalCie', 
						'saldo' => sprintf("%01.2f",$aSaldo['maalcieSaldo']));
				}
				return $return;
			}else{
				return $aSaldo;
			}
		}else{
			return false;
		}
	}
	
	function logBezoek(){
		$uid=$this->getUid();
		$datumtijd=date('Y-m-d H:i:s');
		$locatie='';
		if(isset($_SERVER['REMOTE_ADDR'])){ 
			$ip=$this->_db->escape($_SERVER['REMOTE_ADDR']);
		}else{ 
			$ip='0.0.0.0'; $locatie='';
		}
		if(isset($_SERVER['REQUEST_URI'])){ $url=$this->_db->escape($_SERVER['REQUEST_URI']); }else{ $url=''; }
		if(isset($_SERVER['HTTP_REFERER'])){ $referer=$this->_db->escape($_SERVER['HTTP_REFERER']); }else{ $referer=''; }
		$agent='';
		if(isset($_SERVER['HTTP_USER_AGENT'])){ 
			if(preg_match('/bot/i', $_SERVER['HTTP_USER_AGENT']) OR preg_match('/crawl/i', $_SERVER['HTTP_USER_AGENT']) 
				OR preg_match('/slurp/i', $_SERVER['HTTP_USER_AGENT']) OR preg_match('/Teoma/i', $_SERVER['HTTP_USER_AGENT'])){
				if(preg_match('/google/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='googleBot'; 
				}elseif(preg_match('/msn/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='msnBot'; 
				}elseif(preg_match('/yahoo/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='yahooBot';
				}elseif(preg_match('/Jeeves/i', $_SERVER['HTTP_USER_AGENT'])){ $agent='askJeeves';
				}else{ $agent='onbekende bot';}
			}else{
				if(preg_match('/Windows\ NT\ 5\.1/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows XP | '; 
				}elseif(preg_match('/Windows\ NT\ 5\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows 2K | ';
				}elseif(preg_match('/Win\ 9x/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows 9x | ';
				}elseif(preg_match('/Windows/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Windows anders | ';
				}elseif(preg_match('/Ubuntu\/Dapper/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu Dapper | ';
				}elseif(preg_match('/Ubuntu\/Breezy/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu Breezy | ';
				}elseif(preg_match('/Ubuntu/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Ubuntu | ';
				}elseif(preg_match('/Linux/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Linux | ';
				}elseif(preg_match('/Google\ Desktop/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Google Desktop | ';
				}elseif(preg_match('/Microsoft/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='iets M$ | '; 
				}elseif(preg_match('/Mac\ OS\ X/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='OS X | ';
				}else{ $agent='onbekend | ('.$_SERVER['HTTP_USER_AGENT'].')'; }
				if(preg_match('/Firefox\/1\.5/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF1.5';
				}elseif(preg_match('/Firefox\/1\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF1.0'; 
				}elseif(preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='FF';
				}elseif(preg_match('/Mozilla\/5\.0/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Mozilla';
				}elseif(preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Opera';  
				}elseif(preg_match('/MSIE\ 6\.0/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='IE6';
				}elseif(preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])){ $agent.='IE';
				}elseif(preg_match('/Safari/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.='Safari'; 
				}elseif(preg_match('/Google\ Desktop/', $_SERVER['HTTP_USER_AGENT'])){ $agent.=''; 
				}elseif(preg_match('/Microsoft/i', $_SERVER['HTTP_USER_AGENT'])){ $agent.=''; 
				}else{ $agent.='onbekend ('.$_SERVER['HTTP_USER_AGENT'].')'; }
			}
			
		}
		$sLogQuery="
			INSERT INTO 
				log
			( 
				uid, ip, locatie, moment, url, referer, useragent
			)VALUES(
				'".$uid."', '".$ip."', '".$locatie."', '".$datumtijd."', '".$url."', '".$referer."', '".$agent."'
			);";
		if(!preg_match('/stats.php/', $url) AND $ip!='0.0.0.0'){
			$this->_db->query($sLogQuery);
		}
	}
}
?>
