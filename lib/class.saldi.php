<?php
/*
 * class.saldi.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */


class Saldi{
	private $uid;
	private $cie;
	
	private $data;
	public function __construct($uid, $cie='soccie', $timespan=40){
		$this->uid=$uid;
		$this->cie=$cie;
		$this->load((int)$timespan);
	}
	private function load($timespan){
		$timespan=(int)$timespan;
		if($this->uid=='0000'){
			$sQuery="
				SELECT LEFT(moment, 16) AS moment, SUM(saldo) AS saldo 
				FROM saldolog 
				WHERE cie='".$this->cie."' AND 
					moment>(NOW() - INTERVAL ".$timespan." DAY) GROUP BY LEFT(moment, 16);";
		}else{
			$sQuery="
				SELECT moment, saldo 
				FROM saldolog 
				WHERE uid='".$this->uid."'
				  AND cie='".$this->cie."'
				  AND moment>(NOW() - INTERVAL ".$timespan." DAY);";
		}
		$db=MySql::get_MySql();
		$result=$db->query($sQuery);
		$this->data=$db->result2array($result);
	}
	
	public function getValues(){
		foreach($this->data as $row){
			$return[]=$row['saldo'];
		}
		return $return;
	}
	public function getKeys(){
		foreach($this->data as $row){
			$return[]=str_replace(array('-', ':', ' '), '', $row['moment']);
		}
		return $return;
	}
	public static function putMaalcieCsv($key='CSVSaldi'){
		$db=MySql::get_MySql();
		$lid=Lid::get_lid();
		$sStatus='';
		if(is_array($_FILES) AND isset($_FILES[$key])){
			//bestandje uploaden en verwerken...
			$bCorrect=true;
			//niet met csv functies omdat dat misging met OS-X regeleinden...
			$aRegels=preg_split("/[\s]+/", file_get_contents($_FILES['CSVSaldi']['tmp_name']));
		
			$row=0;
			foreach($aRegels as $regel){
				$regel=str_replace(array('"', ' ', "\n", "\r"), '', $regel);
				$aRegel=explode(',', $regel);
				if($lid->isValidUid($aRegel[0]) AND is_numeric($aRegel[1])){
					$sQuery="
						UPDATE lid 
						SET maalcieSaldo=".$aRegel[1]." 
						WHERE uid='".$aRegel[0]."' 
						LIMIT 1;";
					if($db->query($sQuery)){
						//nu ook nog even naar het saldolog schrijven
						$logQuery="
							INSERT INTO saldolog (
								uid, moment, cie, saldo
							)VALUES(
								'".$aRegel[0]."',
								'".getDateTime()."',
								'maalcie',
								".$aRegel[1]."
							);";
						$db->query($logQuery);
					}else{
						$bCorrect=false; 
					}
					$row++;
				}
			}
		
			if($bCorrect===true){
				$sStatus='Gelukt! er zijn '.$row.' regels ingevoerd; als dit er minder zijn dan u verwacht zitten er ongeldige regels in uw bestand.';
			}else{
				$sStatus='Helaas, er ging iets mis. Controleer uw bestand! mysql gaf terug <'.mysql_error().'>';
			}
		}
		return $sStatus;		
	}
	
	public static function getSaldi($uid, $alleenRood=false){
		$db=MySql::get_MySql();
		
		$query="
			SELECT moment, cie, saldo
			FROM saldolog
			WHERE uid='".$uid."' 
			  AND moment IN(
				SELECT MAX(moment) FROM saldolog WHERE uid='".$uid."'
			  )
			LIMIT 1;";
		$rSaldo=$db->query($query);
		if($rSaldo!==false AND $db->numRows($rSaldo)){
			$aSaldo=$db->next($rSaldo);
			if($alleenRood){
				$return=false;
				if($aSaldo['soccieSaldo']<0){
					$return[]=array(
						'naam' => 'SocCie', 
						'saldo' => sprintf("%01.2f",$aSaldo['soccieSaldo']));
				}
				if($aSaldo['maalcieSaldo']<0){
					$return[]=array(
						'naam' => 'MaalCie', 
						'saldo' => sprintf("%01.2f",$aSaldo['maalcieSaldo']));
				}
				return $return;
			}else{
				return $aSaldo;
			}
		}else{
			return false;
		}
	}
	
}
?>
