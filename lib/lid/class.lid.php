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

	public function __construct($uid){
		if(!$this->isValidUid($uid)){
			throw new Exception('Geen correct [uid:'.$uid.'] opgegeven.');
		}
		$this->uid=$uid;
		$this->load($uid);
	}
	public function load($uid){
		$db=MySql::instance();
		$query="SELECT * FROM lid WHERE uid = '".$db->escape($uid)."' LIMIT 1;";
		$lid=$db->getRow($query);
		if(is_array($lid)){
			$this->profiel=$lid;
			//we unserializeren de array even
			if($this->profiel['instellingen']!=''){
				$this->profiel['instellingen']=unserialize($this->profiel['instellingen']);
			}
		}else{
			throw new Exception('Lid [uid:'.$uid.'] kon niet geladen worden.');
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
				switch($veld){
					case 'instellingen':
						if($value!=''){
							$value=serialize($value);
						}else{
							continue;
						}
					break;
				}
				$row=$veld."=";
				if(is_integer($value)){
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
	public function logChange($diff){
		if($this->hasProperty('changelog')){
			$this->profiel['changelog']=$diff.$this->profiel['changelog'];
		}else{
			$this->profiel['changelog']=$diff;
		}		
	}
	# Sla huidige objecstatus op in LDAP
	public function save_ldap() {
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
				$entry['ou']=$woonoord->getNaam();
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
		}else{
			# Als het een andere status is even kijken of de uid in ldap voorkomt, zo ja wissen
			if($ldap->isLid($this->getUid())){
				$ldap->removeLid($this->getUid());
			}
		}
		$ldap->disconnect();
		return true;
	}
	public function hasProperty($key){	return array_key_exists($key, $this->profiel); }
	public function getProperty($key){
		if(!$this->hasProperty($key)){
			throw new Exception($key.' bestaat niet in profiel');
		}
		return $this->profiel[$key];
	}
	public function setProperty($property, $contents){
		$disallowedProps=array('uid');
		if(!array_key_exists($property, $this->profiel)){ return false; }
		if(in_array($property, $disallowedProps)){ return false; }
		if(is_string($contents)){ $contents=trim($contents); }
		if($property=='password'){
			$this->profiel[$property]=makepasswd($contents);
		}else{
			$this->profiel[$property]=$contents;
		}
		return true;
	}
	public function getUid(){		return $this->profiel['uid']; }
	public function getProfiel(){	return $this->profiel; }
	public function getNaam(){  	return $this->getNaamLink('full','plain'); }
	public function getNickname(){ 	return $this->profiel['nickname']; }
	public function getEmail(){ 	return $this->profiel['email']; }
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
	public function getInstellingen(){ return $this->profiel['instellingen']; }
	
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
			$vorm=Instelling::get('forum_naamWeergave');
		}
		switch($vorm){
			case 'nick':
			case 'bijnaam':
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
			case 'volledig':
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
			case 'aaidrom':
				$voor = array(); preg_match('/^([^aeiuoy]*)(.*)$/', $this->profiel['voornaam'], $voor);
				$achter = array(); preg_match('/^([^aeiuoy]*)(.*)$/', $this->profiel['achternaam'], $achter);
				
				$naam = sprintf("%s%s %s%s%s", $achter[1], $voor[2], 
							($this->profiel['tussenvoegsel'] != '') ? $this->profiel['tussenvoegsel'] . ' ' : '',
							$voor[1], $achter[2]);
			break;
			default:
				$naam='Formaat in $vorm is onbekend.';
		}

		switch($mode){
			case 'link':
				if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
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
	public static function nickExists($nick){
		return Lid::loadByNickname($nick) instanceof Lid;
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
		return true;
	}
}
/*
 * Gebruikersinstellingen opslaan in de Sessie.
 */
class Instelling{

	/*
	 * Instellingarray, een naampje, met een default-value en een type.
	 */
	private static $instellingen=array(
			'layout_rozeWebstek' => array('ja', 'Webstek roze maken', 'enum', array('ja', 'nee')),
			'forum_onderwerpenPerPagina' => array(15, 'Onderwerpen per pagina', 'int', 5), //deze hebben een minimum, anders gaat het forum stuk.
			'forum_postsPerPagina' => array(25, 'Berichten per pagina', 'int', 10),
			'forum_naamWeergave' => array('civitas', 'Naamweergave', 'enum', array('civitas', 'volledig', 'bijnaam')),
			'forum_zoekresultaten' => array(40, 'Zoekresultaten', 'int'),
			'zijbalk_gasnelnaar' => array('ja', 'Ga snel naar weergeven', 'enum', array('ja', 'nee')),
			'zijbalk_mededelingen' => array(8, 'Aantal mededelingen in zijbalk', 'int'),
			'zijbalk_forum' => array(10, 'Aantal forumberichten in zijbalk', 'int'),
			'zijbalk_forum_zelf' => array(0, 'Aantal zelf geposte forumberichten zijbalk', 'int'),
			'zijbalk_verjaardagen' => array(10, 'Aantal verjaardagen in zijbalk', 'int'),
			'voorpagina_maaltijdblokje' => array('ja', 'Volgende maaltijd weergeven', 'enum', array('ja', 'nee')),
			'groepen_toonPasfotos' => array('ja', 'Standaard pasfotos tonen', 'enum', array('ja', 'nee'))
	);

	//hebben we een instelling die $key heet?
	public static function has($key){			return array_key_exists($key, self::$instellingen); }
	public static function getDefault($key){	return self::$instellingen[$key][0]; }
	public static function getDescription($key){return self::$instellingen[$key][1]; }
	public static function getType($key){		return self::$instellingen[$key][2]; }
	public static function getEnumOptions($key){
		if(self::getType($key)=='enum'){
			return self::$instellingen[$key][3];
		}
		return false;
	}
	
	
	public static function get($key){
		//als er nog niets in SESSION staat, herladen.
		if(!isset($_SESSION['instellingen'])){
			self::reload();
		}
		if(!self::has($key)){
			throw new Exception('Deze instelling  bestaat niet');
		}
		//als deze instelling nog niet in SESSION staat, maar we em wel kennen, die er instoppen.
		if(!isset($_SESSION['instellingen'][$key])){
			$_SESSION['instellingen'][$key]=self::getDefault($key);
		}
		return $_SESSION['instellingen'][$key];
	}
	
	public static function set($key, $value){
		if(!isset($_SESSION['instellingen'])){
			self::reload();
		}
		if(!self::has($key)){
			throw new Exception('Deze instelling  bestaat niet');
		}
		switch(self::getType($key)){
			case 'int':
				$value=(int)$value;
				//check op minimum
				if(isset(self::$instellingen[$key][3]) AND $value<self::$instellingen[$key][3]){
					$value=self::$instellingen[$key][3];
				}
			break;
			case 'enum':
				//als $value niet een van de toegestane waarden is
				//de standaardwaarde teruggeven.
				if(!in_array($value, self::getEnumOptions($key))){
					$value=self::getDefault($key);
				}
			break;
		}
		$_SESSION['instellingen'][$key]=$value;
	}
	public static function clear(){
		unset($_SESSION['instellingen']);		
	}
	public static function reload(){
		if(is_array(LoginLid::instance()->getLid()->getInstellingen())){
			$_SESSION['instellingen']=LoginLid::instance()->getLid()->getInstellingen();
		}else{
			$_SESSION['instellingen']=Instelling::getDefaults();
		}
	}
	public static function save(){
		$lid=LoginLid::instance()->getLid();
		$lid->setProperty('instellingen', $_SESSION['instellingen']);
		return $lid->save();
	}	

	//standaardwaarden teruggeven.
	public static function getDefaults(){
		$return=array();
		foreach(self::$instellingen as $key => $instelling){
			$return[$key]=$instelling[0];
		}
		return $return;
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
