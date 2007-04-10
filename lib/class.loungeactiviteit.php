<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.loungeactiviteit.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van loungeactiviteitgegevens
# -------------------------------------------------------------------


class Loungeactiviteit {
	
	var $_db;
	var $_lid;
	
	var $_sError;
	
	function Loungeactiviteit(&$lid, &$db){
		# databaseconnectie openen
		$this->_lid =& $lid;
		$this->_db =& $db;
	}	
	
	function getLoungeactiviteiten(){
		$sActie="
			SELECT
				loungeactiviteit.ID AS ID, 
				loungeactiviteit.naam AS actieNaam, 
				loungeactiviteit.beschrijving AS beschrijving, 
				loungeactiviteit.moment AS moment, 
				loungeactiviteit.limiet AS limiet
			FROM
				loungeactiviteit
			WHERE
				loungeactiviteit.zichtbaar='ja'
			ORDER BY 
				loungeactiviteit.naam ASC;";
		$rActie=$this->_db->query($sActie);
		if($this->_db->numRows($rActie)==0){ return false; }
		while($aActie=$this->_db->next($rActie)){
			$return[]=array(
				'ID' => $aActie['ID'],
				'actieNaam' => $aActie['actieNaam'],
				'beschrijving' => $aActie['beschrijving'],
				'uid' => $aActie['verantwoordelijke'],
				'moment' => $aActie['moment'],
				'limiet' => $aActie['limiet'] );
		}
		return $return;
	}
	
	function getAanmeldingen($iActieID){
		$iActieID=(int)$iActieID;
		$sAanmeldingen="
			SELECT
				loungeactiviteitlid.uid AS uid,
				loungeactiviteitlid.moment AS aanmeldmoment
			FROM
				loungeactiviteitlid 
			WHERE
				loungeactiviteitlid.actieID=".$iActieID.";";
		$rAanmeldingen=$this->_db->query($sAanmeldingen);
		if($this->_db->numRows($rAanmeldingen)==0){ return false; }
		while($aAanmeld =$this->_db->next($rAanmeldingen)){
			$return[]=array(
				'uid' => $aAanmeld['uid'],
				'naamLink' => $this->_lid->getNaamLink($aAanmeld['uid'], 'civitas', true),
				'moment' => $aAanmeld['aanmeldmoment'] );
		}
		return $return;
	}
	
	function meldAan($iActieID, $uid){
		$iActieID=(int)$iActieID;
		if(!preg_match('/[a-z0-9]{4}/', $uid)) return false;
		$sAanmelden="
			INSERT INTO
				loungeactiviteitlid
			(
				actieID, uid, moment
			)VALUES(
				".$iActieID.", '".$uid."', '".getDateTime()."'
			);";
		return $this->_db->query($sAanmelden);
	}
	
	function validateLoungeactiviteit(){
		$validated=true;
		$sError='';
		if(!isset($_POST['actieNaam'], $_POST['beschrijving'], $_POST['limiet'])){
			$sError.='Hela, niet alle velden zijn aanwezig of gevuld.<br />';
			$validated=false;
		}else{
			if(strlen(trim($_POST['actieNaam']))<5){
				$sError.='Het veld <strong>naam</strong> moet 5 of meer tekens bevatten.<br />';
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
			if($_POST['limiet']>48){
				$sError.='Hee, zoveel sjaars zijn er niet dit jaar!<br />';
				$validated=false;
			}
			if($_POST['limiet']<1){
				$sError.='Hee, er moeten wel sjaars meedoen!<br />';
				$validated=false;
			}
		}
		$this->_sError=$sError;
		return $validated;
	}
		
	function newLoungeactiviteit($naam, $beschrijving, $iLimiet){
		//controleren en escapen
		$naam=$this->_db->escape($naam);
		$beschrijving=$this->_db->escape($beschrijving);
		$iLimiet=(int)$iLimiet;
		
		$sNewActie="
			INSERT INTO
				loungeactiviteit
			(
				naam, beschrijving, moment, limiet
			) VALUES (
				'".$naam."', '".$beschrijving."', '".getDateTime()."', ".$iLimiet."
			);";
		return $this->_db->query($sNewActie);
	}
	
	function isVol($iActieID){
		$sIsVol="
			SELECT
				loungeactiviteit.limiet AS limiet,
				count(*) as aantal
			FROM
				loungeactiviteit 
			INNER JOIN 
				loungeactiviteitlid ON(loungeactiviteit.id=loungeactiviteitlid.actieID)
			WHERE
				loungeactiviteit.id=".$iActieID."
			GROUP BY 
				loungeactiviteit.id;";
		$rIsVol=$this->_db->query($sIsVol);
		$aIsVol=$this->_db->next($rIsVol);
		return $this->_db->numRows($rIsVol)==1 AND $aIsVol['limiet']==$aIsVol['aantal'];
	}
	
	function getError(){ return $this->_sError; }
}
?>
