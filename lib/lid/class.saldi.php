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
				WHERE cie='".$this->cie."'
				  AND moment>(NOW() - INTERVAL ".$timespan." DAY)
				GROUP BY LEFT(moment, 16);";
		}else{
			$sQuery="
				SELECT moment, saldo
				FROM saldolog
				WHERE uid='".$this->uid."'
				  AND cie='".$this->cie."'
				  AND moment>(NOW() - INTERVAL ".$timespan." DAY);";
		}
		$this->data=MySql::instance()->query2array($sQuery);
		if(!is_array($this->data)){
			throw new Exception('Saldi::load() gefaald.');
		}
	}
	public function getNaam(){
		switch($this->cie){
			case 'maalcie':	return 'MaalCie'; break;
			case 'soccie':	return 'SocCie'; break;
		}
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
	/*
	 * Geef de grafiektags terug voor in het profiel van een bepaald uid.
	 *  - zelf zie je beide grafieken meteen.
	 *  - maalcie ziet bij iedereen de maalciegrafiek;
	 *  - soccie ziet bij iedereen de socciegrafiek;
	 */
	public static function getGrafiektags($uid){
		$show=false;
		if($uid=='9808' OR LidCache::getLid($uid)->getStatus()!='S_OUDLID'){
			$defer=true; //moeten we er expliciet om vragen (knopje indrukken)
			$show['maalcie']=$show['soccie']=false;
			if(LoginLid::instance()->isSelf($uid)){
				$show['maalcie']=$show['soccie']=true;
				$defer=false;
			}else{
				if(LoginLid::instance()->hasPermission('P_ADMIN,groep:soccie')){
					$show['soccie']=true;
				}
				if(LoginLid::instance()->hasPermission('P_ADMIN,groep:maalcie')){
					$show['maalcie']=true;
				}
			}
		}
		$return='';
		if(is_array($show)){
			foreach($show as $cie => $value){
				if($value){
					$imgtag='<img class="handje" id="'.$cie.'grafiek" src="http://csrdelft.nl/tools/saldografiek.php?uid='.$uid.'&'.$cie.'" onclick="verbreedSaldografiek(\''.$cie.'\');" title="Klik op de grafiek om de tijdspanne te vergroten" />';
					if($defer){
						$return.='<a id="'.$cie.'link" onclick="document.getElementById(\'saldoGrafiek\').innerHTML+=\''.htmlspecialchars(str_replace("'", "\'", $imgtag)).'\'; document.getElementById(\''.$cie.'link\').display=\'none\';" class="knop">'.ucfirst($cie).'grafiek weergeven</a> ';
					}else{
						$return.=$imgtag;
					}
				}
			}
			if($defer){
				$return.='<br /> <div id="saldoGrafiek"></div>';
			}
		}
		return $return;
	}
	public static function putSoccieXML($xml){
		$db=MySql::instance();
		$datum=getDateTime(); //invoerdatum voor hele sessie gelijk.


		$aSocciesaldi=simplexml_load_string($xml);
		//controleren of we wel een object krijgen:
		if(!is_object($aSocciesaldi)){
			return 'Geen correcte XML ingevoerd! (Saldi::putSoccieXML())';
		}

		$iAantal=count($aSocciesaldi);
		$bOk=true;
		foreach($aSocciesaldi as $aSocciesaldo){
			$query="
				UPDATE lid
				SET soccieSaldo=".$aSocciesaldo->saldo."
				WHERE soccieID=".$aSocciesaldo->id."
				  AND createTerm='".$aSocciesaldo->createTerm."' LIMIT 1;";
			//sla het saldo ook op in een logje, zodat we later kunnen zien dat iemand al heel lang
			//rood staat en dus geschopt kan worden...
			$logQuery="
				INSERT INTO saldolog (
					uid, moment, cie, saldo
				)VALUES(
					(SELECT uid FROM lid WHERE soccieID=".$aSocciesaldo->id."  AND createTerm='".$aSocciesaldo->createTerm."' ),
					'".$datum."',
					'soccie',
					".$aSocciesaldo->saldo."
				);";
			if(!$db->query($query)){
				//scheids, er gaet een kwerie mis, ff een feutmelding printen.
				$bOk=false;
			}else{
				if(!$db->query($logQuery)){
					echo '-! Koppeling voor '.$aSocciesaldo->voornaam.' '.$aSocciesaldo->achternaam.' mislukt'."\r\n";
				}
			}

		}
		if($bOk){
			return '[ '.$iAantal.' regels ontvangen.... OK ]';
		}else{
			return '[ tenminste 1 van '.$iAantal.' queries is niet gelukt. Laatste foutmelding was '.mysql_error().']';
		}
	}
	public static function putMaalcieCsv($key='CSVSaldi'){
		$db=MySql::instance();
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
				if(Lid::isValidUid($aRegel[0]) AND is_numeric($aRegel[1])){
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
		$db=MySql::instance();

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
