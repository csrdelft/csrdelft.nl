<?php
/*
 * class.groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * een Groep-object bevat een groep met wat eigenschappen en een array met leden en eventueel functies.
 */
require_once('class.groepen.php');

class Groep{

	//deze array wordt in deze klasse twee keer gebruikt: in __construct() en load()
	private $groepseigenschappen=
		array('groepId', 'gtypeId', 'gtype', 'snaam', 'naam', 'sbeschrijving', 'beschrijving',
			'zichtbaar', 'status', 'begin', 'einde', 'aanmeldbaar', 'limiet', 'toonFuncties');

	private $groep=null;
	private $leden=null;

	public function __construct($init){
		if(!is_array($init) AND preg_match('/^\d+$/', $init)){
			if((int)$init===0){
				//dit zijn de defaultwaarden voor een nieuwe groep.
				$this->groep=array(
					'groepId'=>0, 'snaam'=>'', 'naam'=>'', 'sbeschrijving'=>'', 'beschrijving'=>'',
					'zichtbaar'=>'zichtbaar', 'begin'=>date('Y-m-d'), 'einde'=>'0000-00-00',
					'aanmeldbaar'=>0, 'limiet'=>0, 'toonFuncties'=>'tonen');
				//we moeten ook nog even de groeptypen opzoeken. Die zit als het goed is in GET['gtype'];
				$this->setGtype();
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
				groep.status AS status,  begin, einde, aanmeldbaar, limiet, toonFuncties,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit,
				groeptype.id AS gtypeId, groeptype.naam AS gtype
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			INNER JOIN groeptype ON(groep.gtype=groeptype.id)
			LEFT JOIN lid ON (groeplid.uid=lid.uid)
			WHERE ".$wherePart."
			ORDER BY groeplid.prioriteit ASC, lid.achternaam ASC, lid.voornaam ASC;";
		$rGroep=$db->query($qGroep);
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
					aanmeldbaar, limiet, toonFuncties
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
					".($this->isAanmeldbaar() ? 1 : 0).",
					".(int)$this->getLimiet().",
					'".$this->getToonFuncties()."'
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
					aanmeldbaar=".($this->isAanmeldbaar() ? 1 : 0).",
					limiet=".(int)$this->getLimiet().",
					toonFuncties='".$this->getToonFuncties()."'
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
		return false;
	}

	/*
	 * Groep wegkekken
	 */
	public function delete(){
		if($this->getId()==0){
			die('Kan geen lege groep wegkekken. Groep::delete()');
		}
		$db=MySql::instance();
		$qDeleteLeden="DELETE FROM groeplid WHERE groepid=".$this->getId().";";
		$qDeleteGroep="DELETE FROM groep WHERE id=".$this->getId()." LIMIT 1;";
		$this->leden=$this->groep=null;
		return $db->query($qDeleteLeden) AND $db->query($qDeleteGroep);
	}

	public function getType(){			return $this->groep['gtype']; }
	public function getTypeId(){		return $this->groep['gtypeId']; }

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
	public function isAanmeldbaar(){	return $this->groep['aanmeldbaar']==1; }
	public function getLimiet(){		return $this->groep['limiet']; }
	public function getToonFuncties(){	return $this->groep['toonFuncties']; }

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

	public function setGtype(){
		if(isset($_GET['gtype']) AND Groepen::isValidGtype($_GET['gtype'])){
			$gtypes=Groepen::getGroeptypes();
			foreach($gtypes as $gtype){
				if($gtype['id']==$_GET['gtype'] OR $gtype['naam']==$_GET['gtype']){
					$this->groep=array_merge(
						$this->groep,
						array('gtypeId'=>$gtype['id'], 'gtype'=>$gtype['naam']));
				}
			}
		}else{
			die('Geen gtype opgegeven, niet via de juiste weg aangevraagd...');
		}
	}

	public function setValue($key, $value){
		$fields=array('snaam', 'naam', 'sbeschrijving', 'beschrijving',
			'zichtbaar', 'status', 'begin', 'einde', 'aanmeldbaar', 'limiet', 'toonFuncties');
		if(in_array($key, $fields)){
			$this->groep[$key]=trim($value);
		}
	}

	public function isLid($uid=null){
		if($uid===null){
			$uid=Lid::instance()->getUid();
		}
		return isset($this->leden[$uid]);
	}
	public function toonPasfotos(){ return true; }
	public function isOp($uid){		return $this->isLid($uid) AND $this->leden[$uid]['op']=='1'; }
	public function getLeden(){		return $this->leden; }
	public function getLidCount(){	return count($this->getLeden()); }
	public function isVol(){		return $this->getLimiet()!=0 AND $this->getLimiet()<=$this->getLidCount(); }

	public static function isAdmin(){
		return Lid::instance()->hasPermission('P_LEDEN_MOD');
	}
	public function magBewerken(){
		$lid=Lid::instance();
		return $this->isAdmin() OR $this->isOp($lid->getUid());
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
	/*
	 * Gebruiker mag aanmelden als:
	 *  - de groep aanmeldbaar is
	 *  - de gebruiker leesrechten voor leden heeft
	 *  - de gebruiker nog niet aangemald is
	 *  - de aanmeldlimiet van de groep nog niet overschreden is.
	 *  - de einddatum van de groep groter is dan de huidige datum
	 */
	public function magAanmelden(){
		if($this->isAanmeldbaar()){
			if(Lid::instance()->hasPermission('P_LEDEN_READ') AND !$this->isLid()){
				if($this->getEinde()=='0000-00-00' OR $this->getEinde()>date('Y-m-d')){
					if($this->getLimiet()==0){
						return true;
					}else{
						return !$this->isVol();
					}
				}
			}
		}
		return false;
	}

	public function verwijderLid($uid){
		if(Lid::instance()->isValidUid($uid) AND $this->isLid($uid)){
			$qVerwijderen="
				DELETE FROM
					groeplid
				WHERE
					groepid=".$this->getId()."
				AND
					uid='".$uid."'
				LIMIT 1;";
			return MySql::instance()->query($qVerwijderen);
		}else{
			return false;
		}
	}
	public function meldAan($functie){
		if($this->magAanmelden()){
			return $this->addLid(Lid::instance()->getUid(), $functie);
		}
		return false;
	}

	public function addLid($uid, $functie=''){
		$op=0;
		$functie=str_replace(array("\n","\r"), '', trim($functie));
		switch(strtolower($functie)){
			case 'praeses':	case 'archivaris': case 'werkgroepleider':
			case 'ho': case 'leider': case 'oudste':
				$prioriteit=1;
				$op=1;
			break;
			case 'fiscus': case 'redacteur': case 'bibliothecaris':
			case 'posterman': case 'techniek': case 'abactis':
				$prioriteit=2;
			break;
			case 'computeur': case 'statisticus': case 'provisor':
			case 'internetman': case 'bandleider':
				$prioriteit=3;
			break;
			case 'fotocommisaris':
				$prioriteit=4;
			break;
			case 'koemissaris': case 'stralerpheut': case 'regelneef':
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
					( groepid, uid, op, functie, prioriteit )
				VALUES (
					".$this->getId().", '".$uid."', '".$op."', '".$db->escape($functie)."', ".$prioriteit."
				)";
			return $db->query($sCieQuery);
		}else{
			return false;
		}
	}
	/*
	 * Geef een array met een vorige en een volgende terug.
	 * Dit levert dus vier query's op, niet erg efficient, maar ik optimaliseren kan altijd nog
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
		$rVoorganger=$db->query($qVoorganger);
		if($rVoorganger!==false AND $db->numRows($rVoorganger)==1){
			$aVoorganger=$db->result2array($rVoorganger);
			$return['voorganger']=new Groep($aVoorganger[0]['id']);
		}
		$qOpvolger="
			SELECT id
			FROM groep
			WHERE snaam='".$this->getSnaam()."'
			  AND begin>'".$this->getBegin()."'
			ORDER BY begin ASC
			LIMIT 1;";
		$rOpvolger=$db->query($qOpvolger);
		if($rOpvolger!==false AND $db->numRows($rOpvolger)==1){
			$aOpvolger=$db->result2array($rOpvolger);
			$return['opvolger']=new Groep($aOpvolger[0]['id']);
		}
		return $return;

	}
	public function getLink(){
		return '<a class="groeplink" href="/actueel/groepen/'.$this->getType().'/'.$this->getId().'">'.$this->getNaam().'</a>';
	}

	/*
	 * Deze functie geeft een array terug met functies en aantallen.
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
}
?>
