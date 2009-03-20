<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once('class.ldap.php');
require_once('class.memcached.php');

class Lid implements Serializable{
	private $uid;
	private $profiel;

	/*
	 * Constructor kan worden aangeroepen met $uid='dummy', zo hoeft niet overal
	 * allerlei foutafvangcode geschreven te worden, maar kan het gewoon 'normaal'
	 * dit 'lid' weergeven.
	 */
	public function __construct($uid){
		if(!$this->isValidUid($uid)){
			throw new Exception('Geen correct uid opgegeven.');
		}

		$this->uid=$uid;
		$this->load($uid);
	}
	public function load($uid){
		$db=MySql::instance();
		$query="SELECT * FROM lid WHERE uid = '".$db->escape($uid)."' LIMIT 1";
		$lid=$db->getRow($query);
		if(is_array($lid)){
			$this->profiel=$lid;
		}else{
			throw new Exception('Lid kon niet geladen worden.');
		}
	}
	public static function loadByNickname($nick){
		$db=MySql::instance();
		$query="SELECT uid FROM lid WHERE nickname='".$db->escape($nick)."' LIMIT 1";
		$lid=$db->getRow($query);
		if(is_array($lid)){
			return new Lid($lid['uid']);
		}else{
			return false;
		}
	}
	// sla huidige objectstatus op in db, en update het huidige lid in de LidCache
	public function save(){
		$db=MySql::instance();
		$donotsave=array('uid', 'rssToken');
		$query='UPDATE lid SET ';
		$queryfields=array();
		foreach($this->profiel as $veld => $value){
			if(!in_array($veld, $donotsave)){
				$row=$veld."=";
				if((int)$value==$value){
					$row.=(int)$value;
				}else{
					$row.="'".$db->escape($value)."'";
				}
				$queryfields[]=$row;
			}
		}
		$query.=implode(', ', $queryfields);
		$query.=" WHERE uid='".$this->getUid()."';";
		return $db->query($query) AND LidCache::updateLid($this->getUid());
	}
	# Sla huidige objecstatus op in LDAP
	function save_ldap() {
		require_once 'class.ldap.php';

		$ldap=new LDAP();

		# Alleen leden, gastleden, novieten en kringels staan in LDAP ( en Knorrie öO~ )
		if(preg_match('/^S_(LID|GASTLID|NOVIET|KRINGEL)$/', $this->getStatus()) or $this->getUid()=='9808') {

			# ldap entry in elkaar snokken
			$entry = array();
			$entry['uid'] = $this->getUid();
			$entry['givenname'] = $this->getNaam();
			$entry['sn'] = $this->profiel['achternaam'];
			$entry['cn'] = $this->getNaam();
			$entry['mail'] = $this->getEmail();
			$entry['homephone'] = $this->profiel['telefoon'];
			$entry['mobile'] = $this->profiel['mobiel'];
			$entry['homepostaladdress'] = implode('$',array($this->profiel['adres'],$this->profiel['postcode'],$this->profiel['woonplaats']));
			$entry['o'] = 'C.S.R. Delft';
			$entry['mozillanickname'] = $this->getNickname();
			$entry['mozillausehtmlmail'] = 'FALSE';
			$entry['mozillahomestreet'] = $this->profiel['adres'];
			$entry['mozillahomelocalityname'] =$this->profiel['woonplaats'];
			$entry['mozillahomepostalcode'] = $this->profiel['postcode'];
			$entry['mozillahomecountryname'] = $this->profiel['land'];
			$entry['mozillahomeurl'] = $this->profiel['website'];
			$entry['description'] = 'Ledenlijst C.S.R. Delft';
			$entry['userPassword'] = $this->profiel['password'];


			$woonoord=$this->getWoonoord();
			if($woonoord instanceof Groep){
				$entry['ou']=$groep->getNaam();
			}

			# lege velden er uit gooien
			foreach($entry as $i => $e){
				if($e == ''){ unset ($entry[$i]); }
			}

			# bestaat deze uid al in ldap? dan wijzigen, anders aanmaken
			if($ldap->isLid($entry['uid'])){
				$ldap->modifyLid($entry['uid'], $entry);
			}else{
				$ldap->addLid($entry['uid'], $entry);
			}

			# verbinding sluiten
			$ldap->disconnect();
		}else{
			# Als het een andere status is even kijken of de uid in ldap voorkomt, zo ja wissen
			if($ldap->isLid($this->getUid())){
				$ldap->removeLid($this->getUid());
			}
		}
		$ldap->disconnect();
	}
	//wrappertje voor Instelling, die houdt het ook bij in de SESSIE enzo...
	public function instelling($key){
		return Instelling::get($key);
	}
	public function setProperty($property, $contents){

		$allowedProps=array('achternaam', 'eetwens', 'corvee_wens');
		if(!in_array($property, $allowedProps)){ return false; }
		$contents=trim($contents);
		$this->profiel[$property]=$contents;
	}
	public function getUid(){		return $this->profiel['uid']; }
	public function getProfiel(){	return $this->profiel; }
	public function getNaam(){  	return $this->getNaamLink('full','plain'); }
	public function getNickname(){ 	return $this->profiel['nickname']; }
	public function getEmail(){ 	return $this->profiel['uid']; }
	public function getMoot(){ 		return $this->profiel['moot']; }
	public function getPassword(){	return $this->profiel['password']; }
	public function checkpw($pass){
		// Verify SSHA hash
		$ohash = base64_decode(substr($this->getPassword(), 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash == $nhash) return true;
		return false;
	}
	public function getPermissies(){return $this->profiel['permissies']; }
	public function getStatus(){ return $this->profiel['status']; }
	public function getEetwens(){ return $this->profiel['eetwens']; }
	public function getCorveewens(){ return $this->profiel['corvee_wens']; }
	public function getCorveepunten(){ return $this->profiel['corvee_punten']; }
	public function getCorveevrijstelling(){ return $this->profiel['corvee_vrijstelling']; }
	public function isKwalikok(){ return $this->profiel['corvee_punten']==='1'; }

	public function getWoonoord(){
		require_once 'groepen/class.groepen.php';
		$groepen=Groepen::getGroepenByType(2, $this->getUid());

		if(is_array($groepen) AND isset($groepen[0]['id'])){
			return new Groep($groepen[0]['id']);
		}
		return false;
	}
	public function getSaldi($alleenRood=false){
		$aSaldo=array(
			'soccieSaldo' => $this->profiel['soccieSaldo'],
			'maalcieSaldo' => $this->profiel['maalcieSaldo']);

		$return=false;
		if(!($alleenRood && $aSaldo['soccieSaldo']<0)){
			$return[]=array('naam' => 'SocCie',
				'saldo' => $aSaldo['soccieSaldo']);
		}
		if(!($alleenRood && $aSaldo['maalcieSaldo']<0)){
			$return[]=array('naam' => 'MaalCie',
				'saldo' => $aSaldo['maalcieSaldo']);
		}
		return $return;
	}

	// check of het lid in het bestuur zit.
	public function isBestuur(){
		require_once('groepen/class.groep.php');
		$bestuur=new Groep('bestuur');
		return $bestuur->isLid($uid->getUid());
	}

	/*
	 * getPasfoto()
	 *
	 * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
	 */
	function getPasfoto($imgTag=true, $cssClass='pasfoto'){
		$validExtensions=array('gif', 'jpg', 'jpeg', 'png');

		$pasfoto=CSR_PICS.'pasfoto/geen-foto.jpg';

		foreach($validExtensions as $validExtension){
			if(file_exists(PICS_PATH.'/pasfoto/'.$this->getUid().'.'.$validExtension)){
				$pasfoto=CSR_PICS.'pasfoto/'.$this->getUid().'.'.$validExtension;
				continue;
			}
		}

		if($imgTag===true OR $imgTag==='small'){
			$html='<img class="'.mb_htmlentities($cssClass).'" src="'.$pasfoto.'" ';
			if($imgTag==='small'){
				$html.='style="width: 100px;" ';
			}
			$html.='alt="pasfoto van '.$this->getNaamLink('full', 'html').'" />';
			return $html;
		}else{
			return $pasfoto;
		}
	}
	public function getNaamLink($vorm='full', $mode='plain'){

		$sVolledigeNaam=$this->profiel['voornaam'].' ';
		if($this->profiel['tussenvoegsel']!='') $sVolledigeNaam.=$this->profiel['tussenvoegsel'].' ';
		$sVolledigeNaam.=$this->profiel['achternaam'];


		//als $vorm==='user', de instelling uit het profiel gebruiken voor vorm
		if($vorm=='user'){
			$vorm=Instelling::get('ForumNaamInstelling');
		}
		switch($vorm){
			case 'nick':
				if($this->profiel['nickname']!=''){
					$naam=$this->profiel['nickname'];
				}else{
					$naam=$sVolledigeNaam;
				}
			break;
			//achternaam, voornaam [tussenvoegsel] voor de streeplijst
			case 'streeplijst':
				$naam=$this->profiel['achternaam'].', '.$this->profiel['voornaam'];
				if($this->profiel['tussenvoegsel'] != ''){
					$naam.=' '.$this->profiel['tussenvoegsel'];
				}
			break;
			case 'full':
				$naam=$sVolledigeNaam;
			break;
			case 'civitas':
				if($this->profiel['status']=='S_NOVIET'){
					$naam='Noviet '.$this->profiel['voornaam'];
					if($this->profiel['postfix']!=''){
						$naam.=' '.$this->profiel['postfix'];
					}
				}elseif($this->profiel['status']=='S_KRINGEL' OR $this->profiel['status']=='S_NOBODY'){
					$naam=$sVolledigeNaam;
				}else{
					$naam=($this->profiel['geslacht']=='v') ? 'Ama. ' : 'Am. ';
					if($this->profiel['tussenvoegsel'] != ''){
						$naam.=ucfirst($this->profiel['tussenvoegsel']).' ';
					}
					$naam.=$this->profiel['achternaam'];
					if($this->profiel['postfix'] != '') $naam.=' '.$this->profiel['postfix'];
					if($this->profiel['status']=='S_OUDLID'){ $naam.=' •'; }
					if($this->profiel['status']=='S_KRINGEL'){ $naam.=' ~'; }
				}
			break;
			default:
				$naam='Formaat in $vorm is onbekend.';
		}

		switch($mode){
			case 'link':
				if($this->uid!='dummy' AND LoginLid::instance()->hasPermission('P_LEDEN_READ')){
					return '<a href="/communicatie/profiel/'.$this->getUid().'" title="'.$sVolledigeNaam.'" class="lidLink '.$this->profiel['status'].'">'.mb_htmlentities($naam).'</a>';
				}
			case 'html':
				return mb_htmlentities($naam);
			break;
			case 'plain':
			default:
				return $naam;
		}
	}

	//__toString()-instellingen
	public $tsVorm='full'; //kan zijn full, user, nick, streeplijst
	public $tsMode='plain'; //kan zijn pasfoto, link, html, plain;
	public function __toString(){
		if($this->tsMode=='pasfoto'){
			$this->getPasfoto(true);
		}else{
			return $this->getNaamLink($this->tsVorm, $this->tsMode);
		}
	}
	public function serialize(){
		$lid['uid']=$this->getUid();
		$lid['profiel']=$this->getProfiel();
		return serialize($lid);
	}
	public function unserialize($serialized){
		$lid=unserialize($serialized);
		$this->uid=$lid['uid'];
		$this->profiel=$lid['profiel'];
	}

	public static function isValidUid($uid) {
		return is_string($uid) AND preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}
	public static function exists($uid) {
		if(!Lid::isValidUid($uid)) return false;
		$lid=LidCache::getLid($uid);
		return $lid instanceof Lid;
	}
}

class LidCache{
	private static $instance;

	public $uids=array();

	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance=new LidCache();
		}
		return self::$instance;
	}

	public static function getLid($uid){
		if(!Lid::isValidUid($uid)){
			return false;
		}
		//kijken of we dit lid al in memcached hebben zitten.
		$lid=Memcached::instance()->get($uid);
		if($lid===false){
			try{
				//nieuw lid maken, in memcache stoppen en teruggeven.
				$lid=new Lid($uid);
				Memcached::instance()->set($uid, serialize($lid));
				return $lid;
			}catch(Exception $e){
				return null;
			}
		}
		self::instance()->uids[]=$uid;
		return unserialize($lid);
	}
	public static function flushLid($uid){
		if(!Lid::isValidUid($uid)){
			return false;
		}
		return Memcached::instance()->delete($uid);
	}
	public static function updateLid($uid){
		self::flushLid($uid);
		Memcached::instance()->set($uid, serialize(new Lid($uid)));
	}
}
/*
 * Gebruikersinstellingen opslaan in de Sessie, als ze daar niet inzitten
 * ophalen uit het actieve lid in LoginLid
 */
class Instelling{
	public static function get($key){
		if(!isset($_SESSION['instelligen'][$key])){
			$lid=LoginLid::instance()->getLid();
			$function='get'.$key;
			if(method_exists($lid, $function)){
				$_SESSION['instelling'][$key]=$lid->$function();
			}
			return true;
		}
		return $_SESSION['instelligen'][$key];
	}
	public static function clear(){
		unset($_SESSION['instelligen']);
	}
	public static function save(){

	}

}


class Verjaardag{

	function getVerjaardagen($maand, $dag=0) {
		$db=MySql::instance();
		$maand = (int)$maand; $dag = (int)$dag; $verjaardagen = array();
		$query="
			SELECT
				uid, voornaam, tussenvoegsel, achternaam, nickname, postfix, geslacht, email,
				EXTRACT( DAY FROM gebdatum) as gebdag, status
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				EXTRACT( MONTH FROM gebdatum)= '{$maand}'";
		if($dag!=0)	$query.=" AND gebdag=".$dag;
		$query.=" ORDER BY gebdag;";
		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while($verjaardag=$db->next($result)){
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;
	}

	function getKomende10Verjaardagen() {
		$db=MySql::instance();
		$query="
			SELECT
				uid, nickname, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix, gebdatum,
				ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) AS verjaardag
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC, lidjaar, gebdatum, achternaam
			LIMIT 10";

		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while($aVerjaardag=$db->next($result)){
				$aVerjaardag['jarig_over'] = ceil((strtotime($aVerjaardag['verjaardag'])-time())/86400);
				$aVerjaardag['leeftijd'] = round((strtotime($aVerjaardag['verjaardag'])-strtotime($aVerjaardag['gebdatum']))/31536000);
				$aVerjaardagen[] = $aVerjaardag;
			}
		}
		return $aVerjaardagen;
	}
}
class Moot{

	static function getMaxKringen($moot=0) {
		$db=MySql::instance();
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

		$result = $db->select($sMaxKringen);
		if ($result !== false and $db->numRows($result) > 0) {
			$max = $db->next($result);
			$maxkringen = $max['max'];
			return $maxkringen;
		}else{
			return 0;
		}
	}

	static function getMaxMoten() {
		$db=MySql::instance();
		$query="
			SELECT MAX(moot) as max
			FROM lid
			WHERE (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')";
		$max=$db->getRow($query);
		if(is_array($max)){
			return $max['max'];
		}
        return 0;
	}

	static function getKringen() {
		$db=MySql::instance();
		$kring = array();
		$result = $db->select("
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
		if ($result !== false and $db->numRows($result) > 0) {
			while ($lid = $db->next($result)) {
				$kring[$lid['moot']][$lid['kring']][] = $lid;
			}
		}

		return $kring;
	}
}
class Zoeker{
	function zoekLeden($zoekterm, $zoekveld, $moot, $sort, $zoekstatus = '') {
		$db=MySql::instance();
		$leden = array();
		$zoekfilter='';

		# mysql escape dingesen
		$zoekterm = trim($db->escape($zoekterm));
		$zoekveld = trim($db->escape($zoekveld));

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
		}elseif($zoekveld=='adres'){
			$zoekfilter="adres LIKE '%{$zoekterm}%' OR woonplaats LIKE '%{$zoekterm}%' OR
				postcode LIKE '%{$zoekterm}%' OR REPLACE(postcode, ' ', '') LIKE '%".str_replace(' ', '', $zoekterm)."%'";
		}else{
			if(preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld=='uid' OR $zoekveld=='naam')){
				//zoeken op lichtingen...
				$zoekfilter="SUBSTRING(uid, 1, 2)='".$zoekterm."'";
			}else{
				$zoekfilter="{$zoekveld} LIKE '%{$zoekterm}%'";
			}
		}

		$sort = $db->escape($sort);

		# in welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft

		$statusfilter = '';

		if(is_array($zoekstatus)){
			//we gaan nu gewoon simpelweg statussen aan elkaar plakken. LET OP: deze functie doet nu
			//geen controle of een gebruiker dat mag, dat moet dus eerder gebeuren.
			$statusfilter="status='".implode("' OR status='", $zoekstatus)."'";
		}else{
			# we zoeken in leden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
			if (
				(LoginLid::instance()->hasPermission('P_LEDEN_READ') and !LoginLid::instance()->hasPermission('P_OUDLEDEN_READ') ) or
				(LoginLid::instance()->hasPermission('P_LEDEN_READ') and LoginLid::instance()->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'oudleden')
			   ) {
				$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
			}
			# we zoeken in oudleden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
			if (
				(!LoginLid::instance()->hasPermission('P_LEDEN_READ') and LoginLid::instance()->hasPermission('P_OUDLEDEN_READ') ) or
				(LoginLid::instance()->hasPermission('P_LEDEN_READ') and LoginLid::instance()->hasPermission('P_OUDLEDEN_READ') and $zoekstatus != 'leden')
			   ) {
				if ($statusfilter != '') $statusfilter .= " OR ";
				$statusfilter .= "status='S_OUDLID'";
			}
			# we zoeken in nobodies als
			# de ingelogde persoon dat mag EN daarom gevraagd heeft
			if (LoginLid::instance()->hasPermission('P_OUDLEDEN_MOD') and $zoekstatus === 'nobodies') {
				# alle voorgaande filters worden ongedaan gemaakt en er wordt alleen op nobodies gezocht
				$statusfilter = "status='S_NOBODY'";
			}
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
					kerk, muziek, eetwens, status
				FROM
					lid
				WHERE
					(".$zoekfilter.")
				AND
					($statusfilter)
				{$mootfilter}
				ORDER BY
					{$sort}";
			$result = $db->select($sZoeken);
			if ($result !== false and $db->numRows($result) > 0) {
				while ($lid = $db->next($result)) $leden[] = $lid;
			}
		}

		return $leden;
	}
}
?>
