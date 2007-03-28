<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.inschrijving.php
# -------------------------------------------------------------------
# Haalt de inschrijvingstabel uit de database
#
	# Functies voor class.inschrijving.php
	# uit inschrijving.php
	#  Inschrijving($lid, $db)
	#  isVol($id) 
	#  meldAan($inschrijvingid, $uid)
	#  meldAanMetPartner($inschrijvingid, $uid, $partner(str), $eetwens)
	#  meldAf($inschrijvingid, $uid)
	#  validateInschrijving()
	#  magOrganiseren()
	#  newInschrijving($naam, $datum, $beschrijving, $limiet)
	
	# uit class.inschrijvingcontent.php 
	#  getInschrijvingen()
	#  getAanmeldingen()
	#  getError()
# -------------------------------------------------------------------
	
class Inschrijving {
	var $_db;
	var $_lid;

	var $_sError;
	function Inschrijving(&$lid, &$db){
		# databaseconnectie openen
		$this->_lid =& $lid;
		$this->_db =& $db;		
	}	
	
	function isVol($iInschrijvingID){
		$sIsVol="
			SELECT
				inschrijving.limiet AS limiet,
				count(*) as aantal
			FROM
				inschrijving 
			INNER JOIN 
				inschrijvinglid ON(inschrijving.id=inschrijvinglid.inschrijvingID)
			WHERE
				inschrijving.id=".$iInschrijvingID."
			GROUP BY 
				inschrijving.id;";
		$rIsVol=$this->_db->query($sIsVol);
		$aIsVol=$this->_db->next($rIsVol);
		return $this->_db->numRows($rIsVol)==1 AND $aIsVol['limiet']==$aIsVol['aantal'];
	}
	function meldAan($iInschrijvingID, $uid){
		$iInschrijvingID=(int)$iInschrijvingID;
		if(!preg_match('/[a-z0-9]{4}/', $uid)) return false;
		$sAanmelden="
			INSERT INTO
				inschrijvinglid
			(
				actieID, uid, partner, eetwens_partner
			)VALUES(
				".$iInschrijvingID.", '".$uid."', '', ''
			);";
		return $this->_db->query($sAanmelden);
	}
	function meldAanMetPartner($iInschrijvingID, $uid, $partner, $eetwens){
		$iInschrijvingID=(int)$iInschrijvingID;
		if(!preg_match('/[a-z0-9]{4}/', $uid)) return false;
		$sAanmelden="
			INSERT INTO
				inschrijvinglid
			(
				actieID, uid, partner, eetwens_partner
			)VALUES(
				".$iInschrijvingID.", '".$uid."', '".$partner."', '".$eetwens."'
			);";
		return $this->_db->query($sAanmelden);
	}
	#meldAf
	function meldAf($iInschrijvingID, $uid){
		$iInschrijvingID=(int)$iInschrijvingID;
		if(!preg_match('/[a-z0-9]{4}/', $uid)) return false;
		//aanname : uid is aangemeld.
		$sAfmelden="
			DELETE FROM
				inschrijvinglid
			WHERE
				inschrijvinglid.inschrijvingid = ".$iInschrijvingID."
			AND
				CONVERT(inschrijving.uid USING utf8) = '".$uid."' LIMIT 1;
		";
		return $this->_db->query($sAfmelden);
	}
	
	function validateInschrijving(){
		$validated=true;
		$sError='';
		if(!isset($_POST['inschrijvingNaam'], $_POST['datum'], $_POST['beschrijving'], $_POST['limiet'])){
			$sError.='Hela, niet alle velden zijn aanwezig of gevuld.<br />';
			$validated=false;
		}else{
			if(strlen(trim($_POST['inschrijvingNaam']))<5){
				$sError.='Het veld <strong>naam</strong> moet 5 of meer tekens bevatten.<br />';
				$validated=false;
			}
			if(!preg_match('[0-9][0-9][0-9][0-9]\'-\'[0-9][0-9]\'-\'[0-9][0-9]', $_POST['datum'])){
				$sError.='Het veld <strong>datum</strong> moet van formaat jjjj-mm-dd zijn.<br />';
				$validated=false;
			}
			if(strlen(trim($_POST['beschrijving']))<25){
				$sError.='Het veld <strong>beschrijving</strong> moet 25 of meer tekens bevatten.<br />';
				$validated=false;
			}
			if(!preg_match('/\i*/', $_POST['limiet'])){
				$sError.='Het veld <strong>limiet</strong> moet een getal zijn.<br />';
				$validated=false;
			}
			if($_POST['limiet']>400){
				$sError.='Da\'s nogal optimistisch qua opkomst..<br />';
				$validated=false;
			}
			if($_POST['limiet']<1){
				$sError.='Leden moeten zich wel kunnen inschrijven!<br />';
				$validated=false;
			}			
		}
		$this->_sError=$sError;
		return $validated;
	}
	
	#magOrganiseren()  wie mag er een activiteit toevoegen?
	function magOrganiseren(){
		 return ($this->_lid->hasPermission('P_LEDEN_MOD'));
		 //return true;
	}
	
	# uit class.inschrijvingcontent.php 
	#  getInschrijvingen()
	#  getAanmeldingen()
	#  getError()
	function getInschrijvingen(){
		$sInschrijving="
			SELECT
				inschrijving.ID AS ID, 
				inschrijving.naam AS inschrijvingNaam, 
				inschrijving.beschrijving AS beschrijving, 
				inschrijving.verantwoordelijke AS verantwoordelijke,
				inschrijving.moment AS moment,				 
				inschrijving.limiet AS limiet
			FROM
				inschrijving
			WHERE
				inschrijving.zichtbaar='ja'
			ORDER BY 
				inschrijving.moment ASC;";
		$rInschrijving=$this->_db->query($sInschrijving);	
		echo mysql_error();
		if($this->_db->numRows($rInschrijving)==0){ return false; }
		while($aInschrijving=$this->_db->next($rInschrijving)){
			$return[]=array(
				'ID' => $aInschrijving['ID'],
				'inschrijvingNaam' => $aInschrijving['inschrijvingNaam'],
				'beschrijving' => $aInschrijving['beschrijving'],
				'uid' => $aInschrijving['verantwoordelijke'],
				'naam' => $this->_lid->getCivitasName($aInschrijving['verantwoordelijke']),
				'moment' => $aInschrijving['moment'],
				'limiet' => $aInschrijving['limiet'],
				'partnereis' => $aInschrijving['partnereis'],
				 );
		}
		return $return;
	}
	
	function getAanmeldingen($iInschrijvingID){
		$iInschrijvingID=(int)$iInschrijvingID;
		$sAanmeldingen="
			SELECT
				inschrijvinglid.uid AS uid,
				inschrijvinglid.partner AS partner,
				inschrijvinglid.eetwens_partner AS eetwens_partner
			FROM
				inschrijvinglid 
			WHERE
				inschrijvinglid.inschrijvingID=".$iInschrijvingID.";";
		$rAanmeldingen=$this->_db->query($sAanmeldingen);
		if($this->_db->numRows($rAanmeldingen)==0){ return false; }
		while($aAanmelding =$this->_db->next($rAanmeldingen)){
			$return[]=array(
				'uid' => $aAanmelding['uid'],
				'naam' => $this->_lid->getCivitasName($aAanmelding['uid']),
				'partner' => $aAanmelding['partner'],
				'eetwens_partner' => $aAanmelding['eetwens_partner']);
		}
		return $return;
	}
	
	function getError(){ return $this->_sError; }
}
?>
