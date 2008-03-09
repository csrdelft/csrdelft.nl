<?php
/*
 * class.groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * een Groep-object bevat een groep met wat eigenschappen en een array met leden en eventueel functies.
 */
require_once('class.groepen.php');

class Groep{
	
	private $groepseigenschappen=
		array('groepId', 'gtypeId', 'gtype', 'snaam', 'naam', 'sbeschrijving', 'beschrijving', 
			'zichtbaar', 'status', 'installatie', 'aanmeldbaar', 'limiet');
	
	private $groep=null;
	private $leden=null;
	
	public function __construct($init){
		if(!is_array($init) AND preg_match('/^\d+$/', $init)){
			if((int)$init===0){
				//dit zijn de defaultwaarden voor een nieuwe groep.
				$this->groep=array(
					'groepId'=>0, 'snaam'=>'', 'naam'=>'', 'sbeschrijving'=>'', 'beschrijving'=>'', 
					'zichtbaar'=>'zichtbaar', 'installatie'=>date('Y-m-d'), 'aanmeldbaar'=>0, 'limiet'=>0);
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
		$db=MySql::get_MySql();
		if(preg_match('/^\d+$/', $groepId)){
			$wherePart="groep.id=".(int)$groepId;
		}else{
			//een snaam is niet uniek. Enkel voor h.t. groepen is de snaam uniek, niet voor
			//o.t. vs. h.t. of bij o.t. onderling
			$wherePart="groep.snaam='".$db->escape($groepId)."' AND status='ht'";
		}
		$qGroep="
			SELECT 
				groep.id AS groepId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groep.status AS status, groep.installatie AS installatie, groep.aanmeldbaar AS aanmeldbaar,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit,
				groeptype.id AS gtypeId, groeptype.naam AS gtype
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			INNER JOIN groeptype ON(groep.gtype=groeptype.id)
			WHERE ".$wherePart."
			ORDER BY groeplid.prioriteit ASC, groeplid.uid ASC;";
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
		$db=MySql::get_MySql();
		if($this->getId()==0){
			$qSave="
				INSERT INTO groep (
					snaam, naam, sbeschrijving, beschrijving, gtype, zichtbaar, status, installatie
				) VALUES (
					'".$db->escape($this->getSnaam())."',
					'".$db->escape($this->getNaam())."',
					'".$db->escape($this->getSbeschrijving())."',
					'".$db->escape($this->getBeschrijving())."',
					".$this->getTypeId().",
					'".$db->escape($this->getZichtbaar())."',
					'".$db->escape($this->getStatus())."',
					'".$db->escape($this->getInstallatie())."'
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
					installatie='".$db->escape($this->getInstallatie())."'
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
	
	public function getType(){			return $this->groep['gtype']; }
	public function getTypeId(){		return $this->groep['gtypeId']; }

	public function getId(){			return $this->groep['groepId']; }
	public function getSnaam(){			return $this->groep['snaam']; }
	public function getNaam(){			return $this->groep['naam']; }
	public function getSbeschrijving(){	return $this->groep['sbeschrijving']; }
	public function getBeschrijving(){	return $this->groep['beschrijving']; }
	public function getZichtbaar(){		return $this->groep['zichtbaar']; }
	public function getStatus(){		return $this->groep['status']; }
	public function getInstallatie(){	return $this->groep['installatie']; }
	public function isAanmeldbaar(){	return $this->groep['aanmeldbaar']==1; }
	
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
	public function setSnaam($value){			$this->groep['snaam']=trim($value); }
	public function setNaam($value){			$this->groep['naam']=trim($value); }
	public function setSbeschrijving($value){	$this->groep['sbeschrijving']=trim($value); }
	public function setBeschrijving($value){	$this->groep['beschrijving']=trim($value); }
	public function setZichtbaar($value){		$this->groep['zichtbaar']=trim($value); }
	public function setStatus($value){			$this->groep['status']=trim($value); }
	public function setInstallatie($value){		$this->groep['installatie']=trim($value); }
	
	
	public function isLid($uid){	return isset($this->leden[$uid]); }
	public function isOp($uid){		return $this->isLid($uid) AND $this->leden[$uid]['op']=='1'; }
	public function getLeden(){		return $this->leden; }
	
	public static function isAdmin(){		
		$lid=Lid::get_lid();
		return $lid->hasPermission('P_LEDEN_MOD');
	}
	public function magBewerken(){
		$lid=Lid::get_lid();
		return $this->isAdmin() OR $this->isOp($lid->getUid());
	}
	/*
	 * Kijkt of er naast de huidige groep al een andere groep h.t. is
	 * met dezelfde snaam
	 */
	public function hasHt($snaam=null){
		$db=MySql::get_MySql();
		if($snaam==null){ 
			$this->getSnaam(); 
		}
		$qHasHt="
			SELECT id 
			FROM groep 
			WHERE snaam='".$db->escape($snaam)."' 
			  AND id!=".$this->getId().";";
		$rHasHt=$db->query($qHasHt);
		echo $qHasHt;
		if($db->numRows($rHasHt)!=0){
			return true;
		}
		return false;
	}
	function verwijderLid($uid){
		$lid=Lid::get_lid();
		if($lid->isValidUid($uid)){
			$db=MySql::get_MySql();
			$qVerwijderen="
				DELETE FROM 
					groeplid
				WHERE
					groepid=".$this->getId()."
				AND
					uid='".$uid."' 
				LIMIT 1;";
			return $db->query($qVerwijderen);
		}else{
			return false;
		}
	}
	function addLid($uid, $functie=''){
		$db=MySql::get_MySql();
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
		$db=MySql::get_MySql();
		$qVoorganger="
			SELECT id 
			FROM groep 
			WHERE snaam='".$this->getSnaam()."' 
			  AND installatie<'".$this->getInstallatie()."'
			ORDER BY installatie DESC
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
			  AND installatie>'".$this->getInstallatie()."'
			ORDER BY installatie ASC
			LIMIT 1;";
		$rOpvolger=$db->query($qOpvolger);
		if($rOpvolger!==false AND $db->numRows($rOpvolger)==1){
			$aOpvolger=$db->result2array($rOpvolger);
			$return['opvolger']=new Groep($aOpvolger[0]['id']);
		}
		return $return;
		
	}
}
?>
