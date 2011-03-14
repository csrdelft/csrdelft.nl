<?php
/*
 * class.groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * een Groep-object bevat een groep met wat eigenschappen en een array met leden en eventueel functies.
 */
require_once 'groepen.class.php';

class Groep{

	//deze array wordt in deze klasse twee keer gebruikt: in __construct() en load()
	private $groepseigenschappen=
		array('groepId', 'gtypeId', 'gtype', 'snaam', 'naam', 'sbeschrijving', 'beschrijving',
			'zichtbaar', 'status', 'begin', 'einde', 'aanmeldbaar', 'limiet', 'toonFuncties', 'functiefilter',
			'toonPasfotos', 'lidIsMod');
	private $gtype=null;
	private $groep=null;
	private $leden=null;

	private $stats=null;	//groepstatistieken.

	private $error;

	public function __construct($init){
		if(!is_array($init) AND preg_match('/^\d+$/', $init)){
			if((int)$init===0){
				//dit zijn de defaultwaarden voor een nieuwe groep.
				$this->groep=array(
					'groepId'=>0, 'snaam'=>'', 'naam'=>'', 'sbeschrijving'=>'', 'beschrijving'=>'',
					'zichtbaar'=>'zichtbaar', 'begin'=>date('Y-m-d'), 'einde'=>'0000-00-00',
					'aanmeldbaar'=>'', 'limiet'=>0, 'toonFuncties'=>'tonen', 'functiefilter',
					'toonPasfotos'=>0, 'lidIsMod'=>0);
			}else{
				$this->load($init);
			}
		}elseif(is_string($init)){
			$this->load($init);
		}elseif(is_array($init) AND isset($init[0])){
			$this->groep=array_get_keys($init[0], $this->groepseigenschappen);
			foreach($init as $lid){
				if($lid['uid']!=''){
					$this->leden[$lid['uid']]=array_get_keys($lid, array('uid', 'op', 'functie'));
				}
			}
		}
	}

	/*
	 * Laad een groep in aan de hand van het id of de snaam
	 *
	 * @param	$groepId	integer groepId of string snaam
	 * @return	void
	 */
	public function load($groepId){
		$db=MySql::instance();
		if(preg_match('/^\d+$/', $groepId)){
			$wherePart="groep.id=".(int)$groepId;
		}else{
			//een snaam is niet uniek. Enkel voor h.t. groepen is de snaam uniek, niet voor
			//o.t. vs. h.t. of bij o.t. onderling
			$wherePart="groep.snaam='".$db->escape($groepId)."' AND groep.status='ht'";
		}
		$qGroep="
			SELECT
				groep.id AS groepId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groep.status AS status, begin, einde, aanmeldbaar, limiet, toonFuncties, functiefilter, toonPasfotos, lidIsMod,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit,
				groeptype.id AS gtypeId, groeptype.naam AS gtype
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			INNER JOIN groeptype ON(groep.gtype=groeptype.id)
			LEFT JOIN lid ON (groeplid.uid=lid.uid)
			WHERE ".$wherePart."
			ORDER BY groeplid.prioriteit ASC, lid.achternaam ASC, lid.voornaam ASC;";
		$rGroep=$db->query($qGroep);
		if($db->numRows($rGroep)<1){
			throw new Exception('Groep [groepid:'.mb_htmlentities($groepId).'] bestaat niet.');
		}
		while($aGroep=$db->next($rGroep)){
			//groepseigenschappen worden alleen de eerste iteratie opgeslagen
			if($this->groep===null){
				$this->groep=array_get_keys($aGroep, $this->groepseigenschappen);
			}
			//en ook de leden inladen.
			if($aGroep['uid']!=''){
				$this->leden[$aGroep['uid']]=array_get_keys($aGroep, array('uid', 'op', 'functie'));
			}
		}

	}

	/*
	 * save().
	 * slaat groepinfo op, geen leden! Leden worden direct in de db opgeslagen, niet meer in de instantie
	 * van de klasse bijgeschreven. Pas bij het inladen de volgende keer worden de nieuwe leden in de
	 * instantie van de klasse opgenomen.
	 *
	 * @return			Bool of het gelukt is of niet.
	 */
	public function save(){
		$db=MySql::instance();
		if($this->getId()==0){
			$qSave="
				INSERT INTO groep (
					snaam, naam, sbeschrijving, beschrijving, gtype, zichtbaar, status, begin, einde,
					aanmeldbaar, limiet, toonFuncties, functiefilter, toonPasfotos, lidIsMod
				) VALUES (
					'".$db->escape($this->getSnaam())."',
					'".$db->escape($this->getNaam())."',
					'".$db->escape($this->getSbeschrijving())."',
					'".$db->escape($this->getBeschrijving())."',
					".$this->getTypeId().",
					'".$db->escape($this->getZichtbaar())."',
					'".$db->escape($this->getStatus())."',
					'".$db->escape($this->getBegin())."',
					'".$db->escape($this->getEinde())."',
					'".$db->escape($this->getAanmeldbaar())."',
					".(int)$this->getLimiet().",
					'".$this->getToonFuncties()."',
					'".$db->escape($this->getFunctiefilter())."',
					'".$this->getToonPasfotos()."',
					'".$this->getLidIsMod()."'
				);";
		}else{
			$qSave="
				UPDATE groep SET
					snaam='".$db->escape($this->getSnaam())."',
		 			naam='".$db->escape($this->getNaam())."',
					sbeschrijving='".$db->escape($this->getSbeschrijving())."',
					beschrijving='".$db->escape($this->getBeschrijving())."',
					zichtbaar='".$db->escape($this->getZichtbaar())."',
					status='".$db->escape($this->getStatus())."',
					begin='".$db->escape($this->getBegin())."',
					einde='".$db->escape($this->getEinde())."',
					aanmeldbaar='".$db->escape($this->getAanmeldbaar())."',
					limiet=".(int)$this->getLimiet().",
					toonFuncties='".$this->getToonFuncties()."',
					functiefilter='".$db->escape($this->getFunctiefilter())."',
					toonPasfotos='".$this->getToonPasfotos()."',
					lidIsMod='".$this->getLidIsMod()."'
				WHERE id=".$this->getId()."
				LIMIT 1;";
		}
		if($db->query($qSave)){
			//als het om een nieuwe groep gaat schrijven we het nieuwe id weg in de
			//instantie van het object, zodat we bijvoorbeeld naar dat nieuwe id kunnen refreshen.
			if($this->getId()==0){
				$this->groep['groepId']=$db->insert_id();
			}
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Groep::save()';
		return false;
	}

	/*
	 * Groep wegkekken
	 */
	public function delete(){
		if($this->getId()==0){
			$this->error.='Kan geen lege groep wegkekken. Groep::delete()';
			return false;
		}
		$db=MySql::instance();
		$qDeleteLeden="DELETE FROM groeplid WHERE groepid=".$this->getId().";";
		$qDeleteGroep="DELETE FROM groep WHERE id=".$this->getId()." LIMIT 1;";
		$this->leden=$this->groep=null;
		return $db->query($qDeleteLeden) AND $db->query($qDeleteGroep);
	}

	public function getType(){
		if($this->gtype===null){
			$this->gtype=new Groepen((int)$this->getTypeId());
		}
		return $this->gtype;
	}
	public function getTypeId(){
		if(isset($this->groep['gtypeId'])){
			return $this->groep['gtypeId'];
		}
	}

	public function getId(){			return $this->groep['groepId']; }
	public function getSnaam(){			return $this->groep['snaam']; }
	public function getNaam(){			return $this->groep['naam']; }
	public function getSbeschrijving(){	return $this->groep['sbeschrijving']; }
	public function getBeschrijving(){	return $this->groep['beschrijving']; }
	public function getZichtbaar(){		return $this->groep['zichtbaar']; }
	public function getStatus(){		return $this->groep['status']; }
	public function getBegin(){			return $this->groep['begin']; }
	public function getEinde(){			return $this->groep['einde']; }
	public function getDuration(){
		return strtotime($this->getBegin())-strtotime($this->getEinde())/(60*24*30);
	}
	public function getAanmeldbaar(){	return $this->groep['aanmeldbaar']; }
	public function isAanmeldbaar(){
		return LoginLid::instance()->hasPermission($this->getAanmeldbaar());
	}
	public function getLimiet(){		return $this->groep['limiet']; }
	public function getToonFuncties(){	return $this->groep['toonFuncties']; }
	public function getToonPasfotos(){	return $this->groep['toonPasfotos']; }
	public function toonPasfotos(){
		return $this->isIngelogged() AND $this->getToonPasfotos()==1 AND
			LoginLid::instance()->getLid()->instelling('groepen_toonPasfotos')=='ja';
	}
	public function getLidIsMod(){ 		return $this->groep['lidIsMod']; }

	/*
	 * Geef een bool terug of de functies getoond worden of niet.
	 * Elke groep heeft een veld wat drie waarden kan hebben:
	 *
	 * tonen		Iedereen ziet de functies
	 * verbergen	Alleen admins en groepOps mogen de functies zien.
	 * niet			Functies worden in het geheel verborgen.
	 */
	public function toonFuncties(){
		if($this->getToonFuncties()!='niet'){
			if($this->magBewerken()){
				return true;
			}
			return $this->groep['toonFuncties']=='tonen';
		}
		return false;
	}

	//zet get groeptype, oftewel, groepcategorie.
	public function setGtype($groepen){
		if($groepen instanceof Groepen){
			$this->gtype=$groepen;
			$this->groep['gtypeId']=$groepen->getId();
			return true;
		}else{
			$this->error.='Geen gtype opgegeven, niet via de juiste weg aangevraagd... (Groep::setGtype())';
			return false;
		}
	}

	public function setValue($key, $value){
		$fields=array('snaam', 'naam', 'sbeschrijving', 'beschrijving',
			'zichtbaar', 'status', 'begin', 'einde', 'aanmeldbaar', 'limiet', 'toonFuncties', 'toonPasfotos', 'lidIsMod');
		if(!in_array($key, $fields)){
			throw new Exception('Veld ['.$key.'] is niet toegestaan Groep::setValue()');
		}
		$this->groep[$key]=trim($value);
	}

	public function isLid($uid=null){
		if($uid===null){
			$uid=LoginLid::instance()->getUid();
		}
		return isset($this->leden[$uid]);
	}

	/*
	 * LidIsMod houdt in dat élk lid van een groep leden kan toevoegen
	 * en de groepsbeschrijving kan aanpassen.
	 */
	public function lidIsMod(){
		return $this->getLidIsMod()=='1';
	}
	/*
	 * Een lid is MODerator (of OPerator) van een groep als:
	 * - voor de groep is ingesteld dat elk lid moderator is.
	 * - Bij zijn groepslidmaatschap is aangegeven dat hij moderator is.
	 */
	public function isOp($uid=null){
		if($uid===null){ $uid=LoginLid::instance()->getUid(); }
		if($this->lidIsMod() AND $this->isLid($uid)){ return true; }
		return $this->isLid($uid) AND $this->leden[$uid]['op']=='1';
	}

	public function getLeden(){		return $this->leden; }
	public function getLidObjects(){
		$leden=array();
		if(is_array($this->getLeden())){
			foreach($this->getLeden() as $lid){
				if(Lid::Exists($lid['uid'])){
					$leden[]=LidCache::getLid($lid['uid']);

				}
			}
		}
		return $leden;
	}
	public function getLedenCSV($quotes=false){
		$leden=array();
		if(is_array($this->getLeden())){
			foreach($this->getLeden() as $lid){
				$field='';
				if($quotes){ $field.="'"; }
				$field.=$lid['uid'];
				if($quotes){ $field.="'"; }
				$leden[]=$field;
			}
		}
		return implode($leden, ',');
	}
	public function getLidCount(){	return count($this->getLeden()); }
	public function isVol(){		return $this->getLimiet()!=0 AND $this->getLimiet()<=$this->getLidCount(); }

	public static function isAdmin(){
		return LoginLid::instance()->hasPermission('P_LEDEN_MOD');
	}
	public function magBewerken(){
		return $this->isAdmin() OR $this->isOp(LoginLid::instance()->getUid());
	}
	public function magStatsBekijken(){
		return
			$this->isAdmin() OR
			$this->isOp() OR
			($this->isAanmeldbaar() AND $this->isIngelogged());
	}
	/*
	 * Kijkt of er naast de huidige groep al een andere groep h.t. is
	 * met dezelfde snaam
	 */
	public function hasHt($snaam=null){
		$db=MySql::instance();
		if($snaam==null){
			$snaam=$this->getSnaam();
		}
		$qHasHt="
			SELECT id
			FROM groep
			WHERE snaam='".$db->escape($snaam)."'
			  AND status='ht'
			  AND id!=".$this->getId()."";
		$rHasHt=$db->query($qHasHt);
		if($db->numRows($rHasHt)!=0){
			return true;
		}
		return false;
	}
	public function maakOt(){
		if($this->getStatus()!='ht'){
			$this->error.='Groep o.t. maken mislukt: groep is niet h.t. (Groep::maakOt())';
			return false;
		}else{
			if($this->getEinde()=='0000-00-00'){
				$this->setValue('einde', date('Y-m-d'));
			}
			if($this->isAanmeldbaar()){
				$this->setValue('aanmeldbaar', '');
			}
			$this->setValue('status', 'ot');
			return $this->save();
		}
	}
	/*
	 * Gebruiker mag aanmelden als:
	 *  - de groep aanmeldbaar is
	 *  - de gebruiker leesrechten voor leden heeft
	 *  - de gebruiker nog niet aangemald is
	 *  - de einddatum van de groep groter is dan de huidige datum
	 *  - de aanmeldlimiet van de groep nog niet overschreden is.
	 */
	public function magAanmelden(){
		if(!$this->isAanmeldbaar()) 	return false;
		if(!$this->isIngelogged()) 		return false;
		if($this->isLid()) 				return false;

		if($this->getEinde()=='0000-00-00' OR $this->getEinde()>date('Y-m-d')){
			if($this->getLimiet()==0){
				return true;
			}else{
				return !$this->isVol();
			}
		}
		return false;
	}

	public function verwijderLid($uid){
		if(Lid::isValidUid($uid) AND $this->isLid($uid)){
			$qVerwijderen="
				DELETE FROM groeplid
				WHERE groepid=".$this->getId()."
				  AND uid='".$uid."'
				LIMIT 1;";
			return MySql::instance()->query($qVerwijderen);
		}else{
			return false;
		}
	}
	/*
	 * Bewoners gaan uit een huis weg, en moeten dus naar de oudbewonersgroep verplaatst worden.
	 * Daar hebben normale leden geen rechten voor, en het is een actie met twee stappen. Met
	 * deze functie kan het in een keer.
	 *
	 * @return
	 *
	 * true		bij het succesvol verplaatsen van lid naar eerstvolgende voorganger (ot groep met zelfde snaam).
	 * false 	bij het niet bestaan van een o.t. groep.
	 * 			bij het niet bestaan van het lid in de huidige groep.
	 * 			bij een ongeldig uid.
	 * 			bij het proberen van deze actie door een niet-mod van de huidige groep.
	 */
	public function maakLidOt($uid){
		if(!Lid::isValidUid($uid) OR !$this->isLid($uid)){
			$this->error.='Gegeven uid zit niet in groep of is geen geldig uid. (Groep::maakLidOt())';
			return false;
		}
		if(!$this->magBewerken()){
			$this->error.='Gegeven uid mag deze groep niet bewerken. (Groep::maakLidOt())';
			return false;
		}
		$ot=$this->getOpvolgerVoorganger();
		if(!isset($ot['voorganger'])){
			$this->error.='Groep heeft geen voorganger. (Groep::maakLidOt())';
			return false;
		}
		$ot=$ot['voorganger'];
		if($ot->isLid($uid)){
			$this->error.='O.t. groep bevat dit lid al';
			return false;
		}
		return $ot->addLid($uid) AND $this->verwijderLid($uid);
	}

	public function meldAan($functie){
		if($this->magAanmelden()){
			return $this->addLid(LoginLid::instance()->getUid(), $functie);
		}
		return false;
	}

	/*
	 * Functiefilters.
	 * Groepen worden steeds vaker als inschrijfketzer gebruikt, daardoor
	 * komt er vaak allerlei onzin in het functieveld terecht. Door het
	 * groepfilter
	 */
	public function hasFunctiefilter(){ return $this->getFunctiefilter()!=''; }
	public function getFunctiefilter(){ return $this->groep['functiefilter']; }
	public function getFunctiefilters(){
		if($this->hasFunctiefilter()){
			return explode('|', $this->getFunctiefilter());
		}
		return false;
	}
	public function setFunctiefilter($filters){
		if(!is_array($filters)){
			$this->groep['functiefilter']=$filters;
		}else{
			$this->groep['functiefilter']=implode('|', trim($filters));
		}
	}



	public function addLid($uid, $functie=''){
		$op=0;
		$functie=str_replace(array("\n","\r"), '', trim($functie));
		switch(strtolower($functie)){
			case 'praeses':	case 'archivaris': case 'werkgroepleider': case 'voorzitter':
			case 'ho': case 'leider': case 'oudste':
				$prioriteit=1;
				$op=1;
			break;
			case 'abactis': case 'redacteur': case 'bibliothecaris': case 'secretaris':
			case 'posterman': case 'techniek':
				$prioriteit=2;
			break;
			case 'computeur': case 'statisticus': case 'provisor':
			case 'internetman': case 'bandleider': case 'fiscus': case 'penningmeester':
				$prioriteit=3;
			break;
			case 'fotocommisaris': case 'vice-praeses': case 'koemissaris':
				$prioriteit=4;
			break;
			case 'vice-abactis':
				$prioriteit=5;
			break;
			case 'correspondent': case 'stralerpheut': case 'regelneef':
				$prioriteit=8;
			break;
			case 'q.q.': case 'qq':
				$functie='Q.Q.';
			case 'hj':
				$prioriteit=9;
			break;
			default:
				$prioriteit=5;
			break;
		}
		if(!$this->isLid($uid)){
			$db=MySql::instance();
			$sCieQuery="
				INSERT INTO groeplid
					( groepid, uid, op, functie, prioriteit, moment )
				VALUES (
					".$this->getId().", '".$uid."', '".$op."', '".$db->escape($functie)."', ".$prioriteit.", '".getDateTime()."'
				)";
			return $db->query($sCieQuery);
		}else{
			return false;
		}
	}

	/*
	 * Geef een array met een vorige en een volgende terug.
	 * Dit levert dus vier query's op, niet erg efficient, maar optimaliseren kan altijd nog
	 */
	public function getOpvolgerVoorganger(){
		$return=false;
		$db=MySql::instance();
		$qVoorganger="
			SELECT id
			FROM groep
			WHERE snaam='".$this->getSnaam()."'
			  AND begin<'".$this->getBegin()."'
			ORDER BY begin DESC
			LIMIT 1;";
		$voorganger=$db->getRow($qVoorganger);
		if($voorganger!==false){
			$return['voorganger']=new Groep($voorganger['id']);
		}
		$qOpvolger="
			SELECT id
			FROM groep
			WHERE snaam='".$this->getSnaam()."'
			  AND begin>'".$this->getBegin()."'
			ORDER BY begin ASC
			LIMIT 1;";
		$opvolger=$db->getRow($qOpvolger);
		if($opvolger!==false){
			$return['opvolger']=new Groep($opvolger['id']);
		}
		return $return;

	}

	public function getUrl(){
		return '/actueel/groepen/'.$this->getType()->getNaam().'/'.$this->getId();
	}

	public function getLink($class=''){
		if($class!=''){
			$class='groeplink '.$class;
		}else{
			$class='groeplink';
		}
		return '<a href="'.$this->getUrl().'" class="'.$class.'">'.mb_htmlentities($this->getNaam()).'</a>';
	}

	public function __toString(){
		return $this->getLink();
	}

	/*
	 * Geef een serie links terug voor de in $string gegeven groepid's.
	 *
	 * $string		Door comma's gescheiden groepid's.
	 */
	public static function ids2links($string, $separator=','){
		//$veld mag een enkel id zijn of een serie door komma's gescheiden id's
		$groepen=explode(',', $string);
		$groeplinks=array();
		if(is_array($groepen)){
			foreach($groepen as $groepid){
				$groepid=(int)$groepid;
				if($groepid!=0){
					$groep=new Groep($groepid);
					$groeplinks[]=$groep->getLink();
				}
			}
		}
		return implode($separator, $groeplinks);
	}

	/*
	 * Groepstatistiekjes.
	 * Worden weergegeven in een tabje bij de groepleden.
	 */
	public function getStats($force=false){
		if($force OR $this->stats===null){
			$db=MySql::instance();

			$statqueries=array(
				'totaal' => "SELECT 'Totaal' as totaal, count(*) AS aantal FROM groeplid WHERE groepid=".$this->getId().";",
				'verticale' => "SELECT CONCAT('Verticale ', verticale) AS verticale, count(*) as aantal FROM lid WHERE uid IN(".$this->getLedenCSV(true).") GROUP BY verticale;",
				'geslacht' => "SELECT REPLACE(REPLACE(geslacht, 'm', 'Man'), 'v', 'Vrouw') AS geslacht, count(*) as aantal FROM lid WHERE uid IN( ".$this->getLedenCSV(true).") group by geslacht;",
				'lidjaar' => "SELECT lidjaar, count(*) as aantal FROM lid WHERE uid IN( ".$this->getLedenCSV(true).") group by lidjaar;"
			);

			foreach($statqueries as $key => $query){
				$this->stats[$key]=$db->query2array($query);
			}
		}
		return $this->stats;
	}

	/*
	 * Deze functie geeft een array terug met functies en aantallen.
	 *
	 * Handig als de functie gebruikt wordt voor maten oid.
	 */
	public function getFunctieAantal(){
		$functies=array();
		if(is_array($this->leden)){
			foreach($this->leden as $lid){
				if(!isset($functies[$lid['functie']])){
					$functies[$lid['functie']]=0;
				}
				$functies[$lid['functie']]++;
			}
		}
		return $functies;
	}

	/*
	 * Experimentele groepgeschiedenis.
	 * Een tijdbalkje klussen met de opeenvolgende groepen. Niet afgemaakt.
	 */
	public static function getGroepgeschiedenis($snaam, $limiet=10){
		$db=MySql::instance();
		$limiet=(int)$limiet;
		$groepen=array();
		$qGroepen="
			SELECT
				id, naam, begin, einde,
				EXTRACT( MONTH FROM einde-begin) AS duration
			FROM groep
			WHERE snaam='".$db->escape($snaam)."'
			ORDER BY begin DESC
			LIMIT ".$limiet.";";
		$result=$db->query($qGroepen);
		if ($result !== false and $db->numRows($result) > 0){
			$groepen=$db->result2array($result);
		}
		return $groepen;
	}

	public static function isIngelogged(){
		return LoginLid::instance()->hasPermission('P_LEDEN_READ');
	}

	public function getError(){
		return $this->error;
	}
}
?>
