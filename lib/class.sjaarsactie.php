<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.sjaarsactie.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van sjaarsactiegegevens
# -------------------------------------------------------------------


class Sjaarsactie {

	var $_db;
	var $_lid;

	var $_sError;
	function Sjaarsactie(){
		$this->_lid=Lid::instance();
		$this->_db=MySql::instance();
	}


	function getSjaarsacties(){
		$sActie="
			SELECT
				sjaarsactie.ID AS ID,
				sjaarsactie.naam AS actieNaam,
				sjaarsactie.beschrijving AS beschrijving,
				sjaarsactie.verantwoordelijke AS verantwoordelijke,
				sjaarsactie.moment AS moment,
				sjaarsactie.limiet AS limiet
			FROM
				sjaarsactie
			WHERE
				sjaarsactie.zichtbaar='ja'
			ORDER BY
				sjaarsactie.naam ASC;";
		$rActie=$this->_db->query($sActie);
		if($this->_db->numRows($rActie)==0){ return false; }
		while($aActie=$this->_db->next($rActie)){
			$return[]=array(
				'ID' => $aActie['ID'],
				'actieNaam' => $aActie['actieNaam'],
				'beschrijving' => $aActie['beschrijving'],
				'uid' => $aActie['verantwoordelijke'],
				'naamLink' => $this->_lid->getNaamLink($aActie['verantwoordelijke'], 'civitas', true),
				'moment' => $aActie['moment'],
				'limiet' => $aActie['limiet'] );
		}
		return $return;
	}
	function getAanmeldingen($iActieID){
		$iActieID=(int)$iActieID;
		$sAanmeldingen="
			SELECT
				sjaarsactielid.uid AS uid,
				sjaarsactielid.moment AS aanmeldmoment
			FROM
				sjaarsactielid
			WHERE
				sjaarsactielid.actieID=".$iActieID.";";
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
				sjaarsactielid
			(
				actieID, uid, moment
			)VALUES(
				".$iActieID.", '".$uid."', '".getDateTime()."'
			);";
		return $this->_db->query($sAanmelden);
	}
	function validateSjaarsactie(){
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

	function newSjaarsactie($naam, $beschrijving, $iLimiet){
		//controleren en escapen
		$naam=$this->_db->escape($naam);
		$beschrijving=$this->_db->escape($beschrijving);
		$iLimiet=(int)$iLimiet;

		$sNewActie="
			INSERT INTO
				sjaarsactie
			(
				naam, beschrijving, verantwoordelijke, moment, limiet
			) VALUES (
				'".$naam."', '".$beschrijving."', '".$this->_lid->getUid()."', '".getDateTime()."', ".$iLimiet."
			);";
		return $this->_db->query($sNewActie);
	}
	function isSjaars(){ return $this->_lid->getStatus()=='S_NOVIET'; }
	function isVol($iActieID){
		$sIsVol="
			SELECT
				sjaarsactie.limiet AS limiet,
				count(*) as aantal
			FROM
				sjaarsactie
			INNER JOIN
				sjaarsactielid ON(sjaarsactie.id=sjaarsactielid.actieID)
			WHERE
				sjaarsactie.id=".$iActieID."
			GROUP BY
				sjaarsactie.id;";
		$rIsVol=$this->_db->query($sIsVol);
		$aIsVol=$this->_db->next($rIsVol);
		return $this->_db->numRows($rIsVol)==1 AND $aIsVol['limiet']==$aIsVol['aantal'];
	}
	function isNovCie(){
		//commissieID van de novCie==12
		$sIsNovCie="
			SELECT
				uid
			FROM
				commissielid
			WHERE
				cieid=12
			AND
				uid='".$this->_lid->getUid()."';";
		$rIsNovCie=$this->_db->query($sIsNovCie);
		return $this->_db->numRows($rIsNovCie)==1;
	}
	function getError(){ return $this->_sError; }
}
?>
