<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.lidbeheer.php
# -------------------------------------------------------------------


class LidBeheer{

	var $_db;
	var $_lid;
	var $_commissie;
	
	function LidBeheer(&$lid, &$db){
		$this->_lid =& $lid;
		$this->_db =& $db;
		require_once('class.commissie.php');
		$this->_commissie=new Commissie($this->_db);
	}
	
	function zoekCie($zoekstring){
		if($zoekstring==''){
			//alle cies weergeven.
			$where=' WHERE 1 ';
		}else{
			$where="WHERE naam LIKE '%".$zoekstring."%' ";
		}
		$sQuery="
			SELECT 
				id, naam, titel, stekst
			FROM
				commissie
			".$where."
			ORDER BY
				naam;";
		$rZoekresultaat=$this->_db->query($sQuery);
		if(!($this->_db->numRows($rZoekresultaat)<>0)){
			$sResultaat="Geen commissies gevonden \r\n";
		}else{
			$sResultaat="Commissie(s):\r\n";
			while($aZoekData=$this->_db->next($rZoekresultaat)){
				$sResultaat.=$aZoekData['id'].' - '.$aZoekData['naam']."\r\n";
			}
		}
		return $sResultaat;
	}
	function toonCieLeden($cieID){
		$cieID=(int)$cieID;
		$aCieLeden=$this->_commissie->getCieLeden($cieID);
		if(is_array($aCieLeden)){
			foreach($aCieLeden as $aCieLid){
				echo $aCieLid['uid'].' - '.$aCieLid['naam'];
				if($aCieLid['functie']!='')
					echo ':  '.$aCieLid['functie'];
				echo "\r\n";
			}
		}else{
			return $aCieLeden."\r\n";
		}
	}
	function addToCie($iCieID, $uid, $functie=''){
		$aFunctieToegestaan=array('praeses', 'fiscus', 'provisor', 'posterman', 'internetman', 'archivaris', 
			'redacteur', 'bibliothecaris', 'techniek', 'abactis', 'computeur', 'statisticus', 'bandleider', 'fotocommisaris', 
			'koemissaris', 'lustrumverhaalschrijver', 'stralerpheut', 'regelneef', 'q.q.', 'qq');
		if($functie!='' AND !in_array(strtolower($functie), $aFunctieToegestaan)){
			echo "Functie is niet toegestaan, er zal geen functie ingevoerd worden \r\n";
			$functie='';
		}
		if($this->_commissie->addCieLid($iCieID, $uid, $functie)){
			echo "Commissielid toegevoegd.".mysql_error()."\r\n";
			return true;
		}else{
			echo "Commissielid toevoegen mislukt. Mysql retourneerde: ".mysql_error()."\r\n";
			return false;
		}
	}
	function leegCie($iCieID){
		$iCieID=(int)$iCieID;
		$sQuery="
			DELETE FROM
				commissielid
			WHERE
				cieid=".$iCieID."
			LIMIT 15;";
		return $this->_db->query($sQuery);
	}
	function resetWachtwoord($uid, &$berichten){
		$password=substr(md5(time()), 0, 8);
		$passwordhash=$this->_lid->_makepasswd($password);
		$sQuery="
			SELECT
				voornaam, achternaam, tussenvoegsel, email
			FROM
				lid
			WHERE
				uid='".$uid."'
			LIMIT 1;";
		$rData=$this->_db->query($sQuery);
		if($this->_db->numRows($rData)==1){
			//naam maeken
			$aNaamData=$this->_db->next($rData);
			$sNaam=$aNaamData['voornaam'].' ';
			if($aNaamData['tussenvoegsel']!='') $sNaam.=$aNaamData['tussenvoegsel'].' ';
			$sNaam.=$aNaamData['achternaam'];
			
			$sNieuwWachtwoord="
				UPDATE
					lid
				SET
					password='".$passwordhash."'
				WHERE
					uid='".$uid."'
				LIMIT 1;";
			//wachtwoord hash opslaen
			if($this->_db->query($sNieuwWachtwoord)){
				//gelukt.
				$berichten.="  --wachtwoord gereset. Nu nog een mail sturen naar ".$sNaam." <".$aNaamData['email'].">... \r\n";
		//mail maken
		$mail="
Hallo ".$sNaam.",

U heeft een nieuw wachtwoord aangevraagd voor http://csrdelft.nl. U kunt nu inloggen met de volgende combinatie:

".$uid."
".$password."

U kunt uw wachtwoord wijzigen in uw profiel: http://csrdelft.nl/intern/profiel/".$uid." .

Met vriendelijke groet,

Hanna Timmerarends
h.t. Praeses der Pubcie

P.S. Mocht u nog vragen hebben, dan kan u natuurlijk altijd e-posts sturen naar pubcie@csrdelft.nl";
			mail($aNaamData['email'].', pubcie@csrdelft.nl', 'Nieuw wachtwoord voor de C.S.R.-stek', $mail);
			//$berichten.="\r\n--------------------------------------------\r\n".
				$mail.
				"\r\n--------------------------------------------\r\n";
				$berichten.='gelukt!';
				return true;
			}else{
				$berichten.=mysql_error();
				$berichten.="\r\n  --------------------------------------------\r\n";
				return false;
			}
		}else{
			$berichten.="Lid bestaat niet\r\n";
		}
	}
}//einde classe
?>
