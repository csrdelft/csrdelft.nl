<?php
/*
 * C.S.R. Delft pubcie@csrdelft.nl
 *
 * Lid is een representatie van een lid in de DB. Lid is serializable en wordt door
 * LidCache in memcached gestopt. In principe roept LidCache als enige de
 * constructor van Lid aan.
 *
 * LidCache is een wrappertje om memcached die fijn allemaal Lid-objecten beheert.
 */


require_once 'class.ldap.php';
require_once 'class.memcached.php';
require_once 'class.instellingen.php';
require_once 'class.verticale.php';
require_once 'agenda/class.agenda.php';

class Lid implements Serializable, Agendeerbaar{
	private $uid;
	private $profiel;

	private $kinderen=null;
	
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
			//we unserializeren de instellingen-array even, hmm, lekker vier
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
		if($db->query($query) AND LidCache::updateLid($this->getUid())){
			//als er een patroon is die ook even updaten in de cache, zodat de kindertjes kloppen.
			if($this->getPatroon() instanceof Lid){
				LidCache::updateLid($this->getPatroon()->getUid());
			}
			return true;
		}else{
			return false;
		}
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
			$entry['givenname'] = $this->profiel['voornaam'];
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
	public function getGeslacht(){ 	return $this->profiel['geslacht']; }
	public function getProfiel(){	return $this->profiel; }
	public function getNaam(){  	return $this->getNaamLink('full','plain'); }
	public function getNickname(){ 	return $this->profiel['nickname']; }
	public function getEmail(){ 	return $this->profiel['email']; }
	public function getMoot(){ 		return $this->profiel['moot']; }

	public function isJarig(){		return substr($this->profiel['gebdatum'], 5, 5)==date('m-d'); }
	public function getGeboortedatum(){ return $this->profiel['gebdatum']; }
	
	//we maken een lid Agendeerbaar, zodat het in de agenda kan.
	public function getBeginMoment(){ 
		$jaar=date('Y');
		if(isset($GLOBALS['agenda_jaar'], $GLOBALS['agenda_maand'])){ //FIEES, Patrick. 
			/*
			 * Punt is dat we het goede (opgevraagde) jaar erbij moeten zetten, 
			 * anders gaat het mis op randen van weken en jaren.
			 * De maand is ook nodig, anders gaat het weer mis met de weken in januari, want dan schuift
			 * alles doordat het jaar nog op het restje van de vorige maand staat.
			 */
			$jaar=$GLOBALS['agenda_jaar'];
			if($GLOBALS['agenda_maand']==1 AND substr($this->profiel['gebdatum'],5,2)==$GLOBALS['agenda_maand']){
				$jaar+=1;
			}
		}
		$datum=$jaar.'-'.substr($this->profiel['gebdatum'], 5, 5).' 01:11:11'; // 1 b'vo
		return strtotime($datum); 
	} 
	public function getEindMoment(){ return $this->getBeginMoment()+60; }
	public function getTitel(){ return  $this->getNaamLink('civitas', 'link'); }
	public function getBeschrijving(){ return $this->getTitel().' wordt n'; }
	
	public function getVerticale(){
		return Verticale::$namen[$this->getVerticaleID()];
	}
	public function getVerticaleLetter(){
		return Verticale::$letters[$this->getVerticaleID()];
	}
	public function getVerticaleID(){ return $this->profiel['verticale']; }
	public function isKringleider(){ return $this->profiel['kringleider']!='n'; }
	public function isVerticaan(){ return $this->profiel['motebal']==1; }
	
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
	// Is het huidige lid 'gewoon' lid?
	public function isLid(){
		return in_array($this->getStatus(), array('S_NOVIET', 'S_LID', 'S_GASTLID'));
	}

	/*
	 * Geef een karakter terug om de status van het huidige lid aan te
	 * duiden. In de loop der tijd zijn ~ voor kringel en • voor oudlid
	 * ingeburgerd. Handig om in leden snel te zien om wat voor soort
	 * lid het gaat.
	 */
	public function getStatusChar(){
		switch($this->getStatus()){
			case 'S_OUDLID': return '•';
			case 'S_KRINGEL': return '~';
			case 'S_NOBODY': return '∉';
			case 'S_NOVIET':
			case 'S_GASTLID':
			case 'S_LID': return '∈';
		}
	}
	
	public function getPatroonUid(){	return $this->profiel['patroon']; }
	public function getPatroon(){
		if($this->getPatroonUid()!=''){
			return LidCache::getLid($this->getPatroonUid());
		}else{
			return null;
		}
	}
	/*
	 * Kinderen ophalen voor dit lid. Lazy-loading, er komt een array van
	 * leden in het object te staan. Herladen kan geforceerd worden met
	 * $force=true
	 * 
	 * PAS OP: Als er twee leden met elkaar als patroon ingevuld staan
	 * gaat het hier mis. Deze functie gaat dan oneindig proberen lid-
	 * objecten in de kinder-array te stoppen, waardoor oneindige recursie
	 * ontstaat. PHP geeft daar geen foutmeldingen van. Uit de bugtracker
	 * van PHP: "This was requested before, and this can NOT be done in a
	 * nice way.", wat je dus krijgt is een 500 internal server error,
	 * met in de apache errorlog iets als "premature end of script headers" 
	 */
	public function getKinderen($force=false){
		if($this->kinderen===null or $force){
			$query="SELECT uid FROM lid WHERE patroon='".$this->getUid()."';";
			$result=MySql::instance()->query2array($query);

			$this->kinderen=array();
			if(is_array($result)){
				foreach($result as $row){
					$this->kinderen[]=LidCache::getLid($row['uid']);
				}
			}
		}
		return $this->kinderen;			
	}
	public function getAantalKinderen(){
		if(!is_array($this->getKinderen())){
			//lazy-loading: bij aanroep van deze methode even forceren dat er een aanroep van this->getKinderen geweest is.
			$this->getKinderen();
		}
		return count($this->getKinderen());
	}
	
	//corvee_voorkeuren splitsen en teruggeven als array
	public function getCorveeVoorkeuren(){
		$corvee_voorkeuren = $this->profiel['corvee_voorkeuren'];
		$return = array(
			'ma_kok' => $corvee_voorkeuren[0],
			'ma_afwas' => $corvee_voorkeuren[1],
			'do_kok' => $corvee_voorkeuren[2],
			'do_afwas' => $corvee_voorkeuren[3],
			'theedoek' => $corvee_voorkeuren[4],
			'afzuigkap' => $corvee_voorkeuren[5],
			'frituur' => $corvee_voorkeuren[6],
			'keuken' => $corvee_voorkeuren[7]
		);
		return $return;
	}
	
	public function isKwalikok(){ return $this->profiel['corvee_punten']==='1'; }
	//deze willen we hebben om vanuit templates handig instellingen op te halen.
	public function instelling($key){ return Instelling::get($key); }
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
	
	/*
	 * Zit het huidige lid in de h.t. groep met de korte naam 'bestuur'?
	 */
	public function isBestuur(){
		require_once 'groepen/class.groep.php';
		$bestuur=new Groep('bestuur');
		return $bestuur->isLid($uid->getUid());
	}

	/*
	 * getPasfoto()
	 *
	 * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
	 * Geef anders een standaard-plaatje weer, meestal het logo van C.S.R.
	 */
	function getPasfotoPath(){
		$pasfoto='pasfoto/geen-foto.jpg';
		foreach(array('png', 'jpeg', 'jpg', 'gif') as $validExtension){
			if(file_exists(PICS_PATH.'/pasfoto/'.$this->getUid().'.'.$validExtension)){
				$pasfoto='pasfoto/'.$this->getUid().'.'.$validExtension;
				break;
			}
		}
		return $pasfoto;
	}
	function getPasfoto($imgTag=true, $cssClass='pasfoto'){
		$pasfoto=CSR_PICS.$this->getPasfotoPath();

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

	/*
	 * Maak een link met de naam van het huidige lid naar zijn profiel.
	 *
	 * @vorm:	user, nick, bijnaam, streeplijst, full/volledig, civitas, aaidrom
	 * @mode:	link, html, plain
	 */ 
	public function getNaamLink($vorm='full', $mode='plain'){
		if($this->profiel['voornaam']!=''){
			$sVolledigeNaam=$this->profiel['voornaam'].' ';
		}else{
			$sVolledigeNaam=$this->profiel['voorletters'].' ';
		}
		if($this->profiel['tussenvoegsel']!=''){ $sVolledigeNaam.=$this->profiel['tussenvoegsel'].' '; }
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
					if(LoginLid::instance()->getLid()->getStatus()=='S_NOVIET'){
						$naam=($this->getGeslacht()=='v') ? 'Mevr. ' : 'Dhr. ';
					}else{
						$naam=($this->getGeslacht()=='v') ? 'Ama. ' : 'Am. ';
					}
					if($this->profiel['tussenvoegsel'] != ''){
						$naam.=ucfirst($this->profiel['tussenvoegsel']).' ';
					}
					$naam.=$this->profiel['achternaam'];
					if($this->profiel['postfix'] != '') $naam.=' '.$this->profiel['postfix'];
					if($this->profiel['status']=='S_OUDLID' OR $this->profiel['status']=='S_KRINGEL'){
						$naam.=' '.$this->getStatusChar();
					}
				}
			break;
			case 'aaidrom':
				$voornaam = strtolower($this->profiel['voornaam']);
				$achternaam = strtolower($this->profiel['achternaam']);
				
				$voor = array(); preg_match('/^([^aeiuoy]*)(.*)$/', $voornaam, $voor);
				$achter = array(); preg_match('/^([^aeiuoy]*)(.*)$/', $achternaam, $achter);
				
				$nwvoor = ucwords($achter[1] . $voor[2]);
				$nwachter = ucwords($voor[1] . $achter[2]);

				$naam = sprintf("%s %s%s", $nwvoor, 
							($this->profiel['tussenvoegsel'] != '') ? $this->profiel['tussenvoegsel'] . ' ' : '',
							$nwachter);
			break;
			case 'pasfoto':
				if($mode=='link'){
					$naam=$this->getPasfoto(true, 'lidfoto');
				}else{
					$naam='$vorm [pasfoto] alleen toegestaan in linkmodus';
				}
			break;		
			default:
				$naam='Formaat in $vorm is onbekend.';
		}
		//niet ingelogged nooit een link laten zijn.
		$nolinks=array('x999', 'x101', 'x027', '4444');
		if(in_array($this->getUid(), $nolinks) AND $mode=='link'){
			$mode='html';
		}
		switch($mode){
			case 'link':
				if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
					if($vorm!='pasfoto'){
						$naam=mb_htmlentities($naam);
					}
					return '<a href="/communicatie/profiel/'.$this->getUid().'" title="'.$sVolledigeNaam.'" class="lidLink '.$this->profiel['status'].'">'.$naam.'</a>';
				}
			case 'html':
				if($vorm='pasfoto'){
					return $naam;
				}else{
					return mb_htmlentities($naam);
				}
			break;
			case 'plain':
			default:
				return $naam;
		}
	}

	/*
	 * We kunnen het lid-object type-casten naar string, dan wordt de
	 * naam weeergegeven. We kunnen aangeven op wat voor manier dat moet,
	 * pasfotos kunnen dus ook.
	 *
	 * Gevolg hiervan is dat we in Smarty ook {$lid} kunnen doen en in
	 * PHP gewoon echo $lid;
	 */
	public $tsVorm='full'; //kan zijn full, user, nick, streeplijst
	public $tsMode='plain'; //kan zijn pasfoto, link, html, plain;
	public function __toString(){
		if($this->tsMode=='pasfoto'){
			$this->getPasfoto(true);
		}else{
			return $this->getNaamLink($this->tsVorm, $this->tsMode);
		}
	}

	/*
	 * Het lid-object wordt geserialized opgeslagen in de LidCache,
	 * deze twee functies geven expliciet aan hoe dat serializen en
	 * unserializen moet gebeuren.
	 */
	public function serialize(){
		$lid['uid']=$this->getUid();
		$lid['profiel']=$this->getProfiel();
		$lid['kinderen']=$this->getKinderen();
		return serialize($lid);
	}
	public function unserialize($serialized){
		$lid=unserialize($serialized);
		$this->uid=$lid['uid'];
		$this->profiel=$lid['profiel'];
		$this->kinderen=$lid['kinderen'];
	}

	/*
	 * Simpel testje voor juistheid van een uid. Dit houdt niet in dat een lid
	 * ook werkelijk bestaat, gebruik daarvoor Lid::exists();
	 */
	public static function isValidUid($uid) {
		return is_string($uid) AND preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}

	/*
	 * Bestaat er een lid met uid $uid in de database?
	 */
	public static function exists($uid) {
		if(!Lid::isValidUid($uid)) return false;
		$lid=LidCache::getLid($uid);
		return $lid instanceof Lid;
	}
	/*
	 * Bestaat er al een lid met de bijnaam $nick in de database?
	 */	
	public static function nickExists($nick){
		return Lid::loadByNickname($nick) instanceof Lid;
	}

	/*
	 * Voeg een nieuw regeltje in de lid-tabel in met alleen een nieuw lid-nummer.
	 * PAS OP: niet multi-user safe.
	 */
	public static function createNew($lichting){
		$db=MySql::instance();
		$lichtingid=substr($lichting, 2, 2);
		$query="SELECT max(uid) AS uid FROM lid WHERE LEFT(uid, 2)='".$lichtingid."' LIMIT 1;";
		
		$result=$db->query($query);
		if($db->numRows($result)==1){
			$lid=$db->result2array($result);
			$volgnummer=substr($lid[0]['uid'], 2, 2)+1;			
		}else{
			$volgnummer='1';
		}
		if($volgnummer>99){
			throw new Exception('Teveel leden dit jaar!');
		}

		$newuid=$lichtingid.sprintf('%02d', $volgnummer);

		$changelog='Aangemaakt door [lid='.LoginLid::instance()->getUid().'] op [reldate]'.getDatetime().'[/reldate][br]';

		if($lichting==date('Y')){
			$status='S_NOVIET';
			$perm='P_LID';
		}else{
			$status='S_NOBODY';
			$perm='P_NOBODY';
		}
		$query="
			INSERT INTO lid (uid, lidjaar, studiejaar, status, permissies, changelog, land, o_land)
			VALUE ('".$newuid."', '".$lichting."', '".$lichting."', '".$status."', '".$perm."', '".$changelog."', 'Nederland', 'Nederland');";
		if($db->query($query)){
			return $newuid;
		}else{
			throw new Exception('Kon geen nieuw uid aanmaken.');
		}
	}
	public static function getVerjaardagen($van, $tot, $limiet=0){
		$vanjaar=date('Y', $van);
		$totjaar=date('Y', $tot);
		$van=date('Y-m-d', $van);
		$tot=date('Y-m-d', $tot);
		
		if($limiet>0){
			$limitclause="LIMIT ".(int)$limiet;
		}else{
			$limitclause='';
		}
		$query="
			SELECT uid, ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) as verjaardag
			FROM lid 
			WHERE (
				(CONCAT('".$vanjaar."', SUBSTRING(gebdatum, 5))>='".$van."' AND CONCAT('".$vanjaar."', SUBSTRING(gebdatum, 5))<'".$tot."') 
			OR 
				(CONCAT('".$totjaar."', SUBSTRING(gebdatum, 5))>='".$van."' AND CONCAT('".$totjaar."', SUBSTRING(gebdatum, 5))<'".$tot."') 
			) AND
			(status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') AND
			NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC
			".$limitclause.";";

		$leden=MySql::instance()->query2array($query);
		
		$return=array();
		if(is_array($leden)){
			foreach($leden as $uid){
				$return[]=LidCache::getLid($uid['uid']);
			}
		}
		return $return;
	}
}

class LidCache{
	private static $instance;

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
	public static function flushAll(){
		return Memcached::instance()->flush();
	}
}

class Zoeker{
	function zoekLeden($zoekterm, $zoekveld, $verticale, $sort, $zoekstatus = '', $velden = array()) {
		$db=MySql::instance();
		$leden = array();
		$zoekfilter='';

		# mysql escape dingesen
		$zoekterm = trim($db->escape($zoekterm));
		$zoekveld = trim($db->escape($zoekveld));
		/*TODO: velden checken op rare dingen. Niet dat de velden() array nu buiten code opgegeven kan worden, maar het moet nog wel
		foreach ($velden as &$veld) {
			$veld = trim, escape, lalala
		}*/

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
		$mootfilter = ($verticale != 'alle') ? 'AND verticale=\''.$verticale.'\' ' : '';
		
		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			# standaardvelden
			if (empty($velden)) {
				$velden = array('uid', 'nickname', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix', 'adres', 'postcode', 'woonplaats', 'land', 'telefoon',
					'mobiel', 'email', 'geslacht', 'voornamen', 'icq', 'msn', 'skype', 'jid', 'website', 'beroep', 'studie', 'studiejaar', 'lidjaar',
					'gebdatum', 'moot', 'kring', 'kringleider', 'motebal', 'verticale',
					'o_adres', 'o_postcode', 'o_woonplaats', 'o_land', 'o_telefoon',
					'kerk', 'muziek', 'eetwens', 'status');
			}
		
			# velden kiezen om terug te geven
			$velden_sql = implode(', ', $velden);
			$sZoeken="
				SELECT
					".$velden_sql."
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
