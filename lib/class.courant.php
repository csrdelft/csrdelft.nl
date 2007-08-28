<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.courant.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van courantgegevens
# -------------------------------------------------------------------


class Courant {
	
	private $_db;
	private $_lid;
	
	//huidige courant, 0 is de nog niet verzonden cache.
	private $courantID=0;
	private $berichten=array();
	
	private $sError='';
	private $categorieen=array('bestuur', 'csr', 'overig', 'voorwoord');
	private $catNames=array('Bestuur', 'C.S.R.', 'Overig', 'Voorwoord');
	
	//Constructor voor de courant
	function Courant(){
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
		
		//de berichten uit de cache laden. Dit zal het meest gebeuren.	
		$this->load(0);
	}
	/* 
	 * Courant inladen uit de database.
	 */
	public function load($courantID){
		$this->courantID=(int)$courantID;
		//leegmaken van de berichtenarray
		$this->berichten=array();
		if($this->isCache()){ 
			$sBerichtenQuery="
				SELECT
					ID, titel, cat AS categorie, bericht, datumTijd, uid, volgorde
				FROM
					courantcache
				WHERE 
					1
				ORDER BY
					cat, volgorde, datumTijd;";
		}else{ 
			$sBerichtenQuery="
				SELECT
					courant.ID AS mailID,
					courant.verzendMoment AS verzendMoment,
					courant.verzender AS verzendUid,
					courant.template AS template,
					courantbericht.ID AS ID,
					titel, 
					cat AS categorie, 
					bericht, 
					datumTijd, 
					courantbericht.uid AS berichtUid, 
					volgorde
				FROM
					courant, courantbericht
				WHERE 
					courant.ID=".$this->getID()." 
				AND 
					courant.ID=courantbericht.courantID
				ORDER BY
					cat, volgorde, datumTijd;";
		}
		$rBerichten=$this->_db->query($sBerichtenQuery);
		if($this->_db->numRows($rBerichten)>=1){
			while($aBericht=$this->_db->next($rBerichten)){
				$this->berichten[$aBericht['ID']]=$aBericht;
			}
			return true;
		}
		return false;
	}
	
	public function getID(){ return $this->courantID; }
	public function getError(){ return $this->sError; }
	public function isCache(){ return $this->courantID==0; }
	public function getCats($nice=false){
		if($nice){
			$return=$this->catNames;
		}else{
			$return=$this->categorieen;
		}
		//voorwoord eruit gooien
		if(!$this->magBeheren()){ 
			unset($return[3]);
		}
		return $return; 
	}
	
	public function getNaam($uid){ return $this->_lid->getNaamLink($uid); }
	public function getTemplatePath(){
		$return=SMARTY_TEMPLATE_DIR.'courant/mail/';
		if(isset($this->berichten[0]['template']) AND file_exists($return.$this->berichten[0]['template'])){
			$return.=$this->berichten[0]['template'];
		}else{
			$return.=COURANT_TEMPLATE;
		}
		return $return;
	}
	
	function magToevoegen(){ return $this->_lid->hasPermission('P_MAIL_POST'); }
	function magBeheren(){ return $this->_lid->hasPermission('P_MAIL_COMPOSE'); }
	function magVerzenden(){ return $this->_lid->hasPermission('P_MAIL_SEND'); }
	
	private function _isValideCategorie($categorie){ return in_array($categorie, $this->categorieen); }
	
	private function clearTitel($titel){
		//titel escapen, eerste letter een hoofdletter maken, en de spaties wegkekken
		return ucfirst($this->_db->escape(trim($titel)));
	}
	private function clearBericht($bericht){
		//bericht escapen, eerste letter een hoofdletter maken, en de spaties wegkekken
		return ucfirst($this->_db->escape(trim($bericht)));
	}
	private function clearCategorie($categorie){
		if($this->_isValideCategorie($categorie)){ 
			return $categorie;
		}else{
			return 'overig'; 
		}	
	}
	function addBericht($titel, $categorie, $bericht){
		//berichten invoeren mag enkel in de cache	
		if(!$this->isCache()){ 
			$this->sError='Berichten mogen enkel in de cache worden ingevoerd. (Courant::addBericht())';			
			return false; 
		}
	
		//volgorde van berichten bepalen:
		$volgorde=0;
		//agenda altijd helemaal bovenaan
		if(strtolower(trim($titel))=='agenda'){ $volgorde=-1000; }
		//andere dingen naar achteren
		if(preg_match('/kamer/i', $titel)){ $volgorde=99; }
		if(preg_match('/ampel/i', $titel)){ $volgorde=999; }
		
		$sBerichtQuery="
			INSERT INTO
				courantcache
			( 
				uid, titel, cat, bericht, datumTijd, volgorde
			)VALUES(
				'".$this->_lid->getUid()."', '".$this->clearTitel($titel)."', 
				'".$this->clearCategorie($categorie)."', '".$this->clearBericht($bericht)."', '".getDateTime()."', ".$volgorde."
			);";
		
		return $this->_db->query($sBerichtQuery);
	}
	public function isZichtbaar($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		if($this->isCache()){
			if($this->magBeheren()){ return true; }
			if(!isset($this->berichten[$iBerichtID])){
				$this->sError='Bericht staat niet in cache (Courant::isBewerkbaar())';			
			}else{
				if($this->berichten[$iBerichtID]['uid']!=$this->_lid->getUid()){
					$this->sError='U mag geen berichten van anderen aanpassen. (Courant::isBewerkbaar())';
				}else{
					return true;
				}
			}
		}else{
			$this->sError='Berichten mogen enkel in de cache worden ingevoerd. (Courant::isBewerkbaar())';			
		}
		return false; 
	}
	function bewerkBericht($iBerichtID, $titel, $categorie, $bericht){
		$iBerichtID=(int)$iBerichtID;
		if(!$this->isZichtbaar($iBerichtID)){ return false; }
		$sBerichtQuery="
			UPDATE
				courantcache
			SET
				titel='".$this->clearTitel($titel)."', 
				cat='".$this->clearCategorie($categorie)."', 
				bericht='".$this->clearBericht($bericht)."', 
				datumTijd='".getDateTime()."'
			WHERE
				ID=".$iBerichtID."
			LIMIT 1;"; 
		return $this->_db->query($sBerichtQuery);
		
	}
	
	function valideerBerichtInvoer(){
		$bValid=true;
		if(isset($_POST['titel']) AND isset($_POST['categorie']) AND isset($_POST['bericht'])){
			if(strlen(trim($_POST['titel'])) < 2 ){
				$bValid=false;
				$this->sError.='Het veld <strong>titel</strong> moet minstens 2 tekens bevatten.<br />';
			}
			if(strlen(trim($_POST['bericht'])) < 15 ){
				$bValid=false;
				$this->sError.='Het veld <strong>bericht</strong> moet minstens 15 tekens bevatten.<br />';
			}
		}else{
			$bValid=false;
			$this->sError.='Het formulier is niet compleet<br />';
		}
		return $bValid;
	}
	
	function getVerzendmoment(){
		if(!$this->isCache()){
			//beetje ranzige manier om het eerste element van de array aan te spreken
			$first=current($this->berichten);
			return $first['verzendMoment'];
		}else{
			$this->sError='De cache is nog niet verzonden, dus heeft geen verzendmoment (Courant::getVerzendMoment())';
			return false;
		}
	}	
	
	public function getBerichten(){
		if(!is_array($this->berichten)){
			$this->sError='Er zijn geen berichten ingeladen (Courant::getBerichten())';
			return false;
		}
		return $this->berichten;
	}
	/*
	 * Geef de berichten uit de cache terug die de huidige gebruiker mag zien.
	 * Als de gebruiker beheerder of bestuur is mag de gebruiker alle berichten zien.
	 */
	function getBerichtenVoorGebruiker(){
		if($this->isCache()){
			$userCache=array();
			//mods en bestuur zien alle berichten
			if($this->magBeheren() OR $this->_lid->isBestuur()){
				return $this->berichten;
			}else{
				foreach($this->berichten as $bericht){
					if($this->_lid->getUid()==$bericht['uid']){
						$userCache[]=$bericht;
					}
				}
				return $userCache;
			}
		}else{
			$this->sError='Buiten de cache kan niets bewerkt worden (Courant::getBerichtenVoorGebruiker()).';
			return false;
		}
	}
	
	function getBericht($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		if(!$this->isZichtbaar($iBerichtID)){ return false; }
		return $this->berichten[$iBerichtID];
	}
	
	function verwijderBericht($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		if(!$this->isZichtbaar($iBerichtID)){ return false; }
		$sBerichtVerwijderen="
			DELETE FROM
				courantcache
			WHERE
				ID=".$iBerichtID."
			LIMIT 1;";
		$this->_db->query($sBerichtVerwijderen);
		return mysql_affected_rows()==1;
	}
	
	

	
	/*
	 * functie rost alles vanuit de tabel courantcache naar de tabel 
	 * courant en courantbericht, zodat ze daar bewaard kunnen worden ter archivering.
	 */
	public function leegCache(){
		if(count($this->getBerichten())==0){
			$this->sError='Courant bevat helemaal geen berichten (Courant::leegCache())';			
			return false;
		}
		$iCourantID=$this->createCourant();
		if(is_integer($iCourantID)){
			//kopieren dan maar
			foreach($this->getBerichten() as $aBericht){
				$sMoveQuery="
					INSERT INTO
						courantbericht
					(
						courantID, titel, cat, bericht, volgorde, uid, datumTijd
					)VALUES(
						".$iCourantID.", 
						'".$this->clearTitel($aBericht['titel'])."', 
						'".$this->clearCategorie($aBericht['cat'])."', 
						'".$this->clearBericht($aBericht['bericht'])."', 
						'".$aBericht['volgorde']."',
						'".$aBericht['uid']."',
						'".$aBericht['datumTijd']."'
					);";
				$this->_db->query($sMoveQuery);
			}//einde foreach $aBerichten
			//cache leeggooien:
			$sClearCache="TRUNCATE TABLE courantcache;";
			$this->_db->query($sClearCache);
			return $iCourantID;
		}else{
			return false;
		}
	}
	
	private function createCourant(){
		$uid=$this->_lid->getUid();
		$datumTijd=getDateTime();
		$sCreatecourantQuery="
			INSERT INTO
				courant
			( 
				verzendMoment, verzender, template
			) VALUES (
				'".$datumTijd."', '".$uid."', '".CSRMAIL_TEMPLATE."'
			);";
		if($this->_db->query($sCreatecourantQuery)){
			return $this->_db->insert_id();
		}else{
			return false;
		}
	}
	
	################################################################
	###	Archief-methodes, heeft niets meer met de huidige instantie
	### te maken.
	################################################################
	function getArchiefmails($iJaar = null){
		if($iJaar!=null){
			$sQueryJaar="WHERE YEAR(verzendMoment) = ".$iJaar;
		}
		
		$sArchiefQuery="
			SELECT
				ID, verzendMoment, verzender
			FROM 
				courant
			".$sQueryJaar."
			ORDER BY 
				verzendMoment DESC;";
		$rArchief=$this->_db->query($sArchiefQuery);
		
		if($this->_db->numRows($rArchief)==0){
			return false;
		}else{
			return $this->_db->result2array($rArchief);
		}
	}
	
	function getArchiefjaren(){
		$sJarenQuery="
			SELECT
				DISTINCT YEAR(verzendMoment) AS jaar
			FROM
				courant
			ORDER BY
				verzendMoment DESC;";
	}
	
	
}//einde classe Courant
?>
