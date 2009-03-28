<?php
/*
 * class.loginlid.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Bewaart het huidige ingeloggede lid, inloggen, uitloggen, rechten.
 *
 */
require_once 'class.lid.php';

class LoginLid{
	private static $instance;

	# permissies die we gebruiken om te vergelijken met de permissies van
	# een gebruiker. zie functie _loadPermissions()
	protected $_permissions = array();
	protected $_perm_user   = array();

	private $lid;

	public static function instance(){
		//als er nog geen instantie gemaakt is, die nu maken
		if(!isset(self::$instance)){
			self::$instance=new LoginLid();
		}
		return self::$instance;
	}
	private function __construct(){
		$this->_loadPermissions();

		# http://www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
		if (session_id() == 'deleted') session_regenerate_id();

		//Staat er een gebruiker in de sessie?
		if(!$this->userIsActive()){
			# zo nee, dan nobody user er in gooien...
			# in dit geval is het de eerste keer dat we een pagina opvragen
			# of er is net uitgelogd waardoor de gegevens zijn leeggegooid
			$this->login('x999', 'x999', false);
		}
	}
	private function userIsActive(){
		//er is geen _uid gezet in _SESSION dus er is nog niemand ingelogged.
		if(!isset($_SESSION['_uid'])){
			return false;
		}
		//Sessie is gekoppeld aan ip, het ip checken:
		if(isset($_SESSION['_ip']) AND $_SERVER['REMOTE_ADDR'] != $_SESSION['_ip']){
			return false;
		}
		$lid=LidCache::getLid($_SESSION['_uid']);
		if($lid instanceof Lid){
			$this->lid=$lid;
			return true;
		}else{
			return false;
		}
	}
	public function getUid(){
		return $this->lid->getUid();
	}
	public function getLid(){
		return $this->lid;
	}
	public function isSelf($uid){
		return $this->lid->getUid()==$uid;
	}

	//TODO: marco gaat dit goed fixen
	public function getForumLaatstBekeken(){
		if($this->getUID()=='x999'){
			return time();
		}else{
			return strtotime($this->lid->getProperty('forum_laatstbekeken'));
		}
	}
	public function updateForumLaatstBekeken(){
		if($this->getUID()!='x999'){
			$this->lid->setProperty('forum_laatstbekeken', date('Y-m-d H:i:s'));
			$this->lid->save();
		}
	}

	# dispatch the login proces to a separate function based on MODE
	public function login($user, $pass = "", $checkip = true) {
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

	# als een gebruiker wordt ingelogd met ipcheck==true, dan wordt het IP-adres
	# van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal alleen
	# vanaf dat adres toegang geven tot de website
	private function _login_web($user, $pass, $checkip = true) {
		$lid=false;
		//eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if(Lid::isValidUid($user)){
			$lid=LidCache::getLid($user);
		}
		//als er geen lid-object terugkomt, proberen we het met de nickname:
		if(!($lid instanceof Lid)){
			$lid=Lid::loadByNickname($user);
			if(!($lid instanceof Lid)){
				return false;
			}
		}

		# we hebben nu een gebruiker gevonden en gaan eerst het wachtwoord controleren
		if(!$lid->checkpw($pass)){
			return false;
		}

		# als dat klopt laden we het profiel in en richten de sessie in
		$this->lid=$lid;
		$_SESSION['_uid'] = $lid->getUid();

		# sessie koppelen aan ip?
		if($checkip == true){
			$_SESSION['_ip'] = $_SERVER['REMOTE_ADDR'];
		}elseif(isset($_SESSION['_ip'])){
			unset($_SESSION['_ip']);
		}
		Instelling::reload();
		return true;
	}

	# login without a password, only for BOT use
	# only uids are supported, no nicknames
	private function _login_bot($user) {
		$lid=false;
		//eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if(Lid::isValidUid($user)){
			$lid=LidCache::getLid($user);
			if($lid instanceof Lid){
				$this->lid=$lid;
				return true;
			}
		}
		return false;
	}

	# TODO: implement this
	private function _login_cli($user) {
		return false;
	}
	public function logout() {
		session_unset();
		$this->login('x999','x999',true);
	}
	public function instelling($key){
		return Instelling::get($key);
	}
	public function hasPermission($descr, $liddescr=null) {
		# zoek de rechten van de gebruiker op
		if($liddescr==null){
			$liddescr=$this->lid->getPermissies();
		}

		# ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!array_key_exists($liddescr, $this->_perm_user)) return false;
		# zoek de code op
		$lidheeft = $this->_perm_user[$liddescr];

		# Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
		# gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
		# dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
		# gebruiker heeft hij ook rechten.
		$permissies=explode(',', $descr);
		foreach($permissies as $permissie){
			$permissies=trim($permissie);
			if($permissie==$this->lid->getUid()){
				return true;
			}elseif(substr($permissie, 0, 5)=='groep'){
				require_once 'groepen/class.groep.php';
				$groep=new Groep(substr($permissie, 6));
				return $groep->isLid();
			}
			# ga alleen verder als er een geldige permissie wordt gevraagd
			if (array_key_exists($permissie, $this->_permissions)){
				# zoek de code op
				$gevraagd = (int) $this->_permissions[$permissie];

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
				$resultaat=$gevraagd & $lidheeft;

				if($resultaat==$gevraagd){
					return true;
				}
			}
		}
		# Zo niet... dan niet
		return false;
	}
	private function _loadPermissions() {
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
			'P_MAIL_POST'    => 01000000000, # mag berichtjes in de pubciemail rossen
			'P_MAIL_COMPOSE' => 03000000000, # mag alle berichtjes in de pubcie-mail bewerken, en volgorde wijzigen
			'P_MAIL_SEND'    => 07000000000, # mag de C.S.R.-mail verzenden
			'P_BIEB_READ'    => 00000000010, # Bibliotheek lezen
			'P_BIEB_EDIT'    => 00000000030, # Bibliotheek wijzigen
			'P_BIEB_MOD'     => 00000000070, # Bibliotheek zowel wijzigen als lezen
			# N.B. bij uitbreiding van deze octale getallen met nog een cijfer erbij gaat er iets mis, wat weten we nog niet.
		);

		# Deze waarden worden samengesteld uit bovenstaande permissies en
		# worden in de gebruikersprofielen gebruikt als aanduiding voor
		# welke permissie-groep de gebruiker in zit.

		$p = $this->_permissions;
		$this->_perm_user = array(
			'P_NOBODY'     => $p['P_NOBODY'] | $p['P_FORUM_READ'] | $p['P_AGENDA_READ'],
			'P_LID'        => $p['P_LOGGED_IN'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_DOCS_READ'] | $p['P_LEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_AGENDA_POST'] | $p['P_MAAL_WIJ'] | $p['P_MAIL_POST'] | $p['P_BIEB_READ'],
			'P_OUDLID'     => $p['P_LOGGED_IN'] | $p['P_LEDEN_READ'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_PROFIEL_EDIT'] | $p['P_FORUM_READ'] | $p['P_MAIL_POST'] | $p['P_AGENDA_READ'],
			'P_MODERATOR'  => $p['P_ADMIN'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_AGENDA_MOD'] | $p['P_MAAL_MOD'] | $p['P_MAIL_SEND'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD']
		);
		# extra dingen, waarvoor de array perm_user zelf nodig is
		$this->_perm_user['P_PUBCIE']  = $this->_perm_user['P_MODERATOR'];
		$this->_perm_user['P_MAALCIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];
		$this->_perm_user['P_BESTUUR'] = $this->_perm_user['P_LID'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_READ'] | $p['P_NEWS_MOD'] | $p['P_MAAL_MOD'] | $p['P_MAIL_COMPOSE'] | $p['P_AGENDA_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'];
		$this->_perm_user['P_VAB']     = $this->_perm_user['P_BESTUUR']  | $p['P_OUDLEDEN_MOD'];
		$this->_perm_user['P_KNORRIE'] = $this->_perm_user['P_LID'] | $p['P_MAAL_MOD'];
		$this->_perm_user['P_ETER']	   = $this->_perm_user['P_NOBODY'] | $p['P_LOGGED_IN'] | $p['P_MAAL_IK'] | $p['P_PROFIEL_EDIT'];

	}

	//met een token is het mogelijk rss feeds te zien te krijgen zonder ingelogged te zijn.
	//permissies worden overgenomen van het lid dat het token heeft, het token staat in het profiel
	//van een lid.
	private $tokenCache;
	public function validateWithToken($token, $perm){
		if(!preg_match('/[a-z0-9]{25}/', $token)){
			return false;
		}
		if(!isset($this->tokenCache[$token])){
			$query="SELECT uid, permissies FROM lid WHERE rssToken='".$token."' LIMIT 1;";
			$this->tokenCache[$token]=MySql::instance()->getRow($query);
		}
		return $this->hasPermission($perm, $this->tokenCache[$token]['permissies']);
	}

	public function getToken($uid=null){
		if($uid==null){ $uid=$this->getUid(); }
		$token=substr(md5($uid.getDateTime()), 0, 25);
		$query="UPDATE lid SET rssToken='".$token."' WHERE uid='".$uid."' LIMIT 1;";
		if(MySql::instance()->query($query)){
			LidCache::flushLid($uid);
			return $token;
		}else{
			return false;
		}
	}
	
	//maakt een permissiestring met uid's enzo wat leesbaarder
	public static function formatPermissionstring($string){
		$parts=explode(',', $string);
		$return=array();
		require_once 'groepen/class.groep.php';
		foreach($parts as $part){
			if(Lid::isValidUid($part)){
				$lid=LidCache::getLid($part);

				$return[]=(string)$lid;
			}elseif(substr($part, 0, 5)=='groep'){
				$groep=new Groep(substr($part, 6));
				if($groep->getId()!=0){
					$return[]=$groep->getLink();
				}
			}else{
				$return[]=$part;
			}
		}
		return implode(', ', $return);
	}
}
?>
