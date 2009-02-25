<?php
/*
 * soccie.php.
 *
 * Dit bestand is de ingang voor anthrax om de saldi te uploaden, elke morgen
 * om 6:30 (cronjob op anthrax). Hier worden de saldi uit de XML getrokken die
 * van Anthrax komt en in de betreffende tabellen in de database gestopt.
 *
 * TODO: saldoupdatefunctionaliteit naar Lid verplaatsen.
 */
error_reporting(E_ALL);


require_once 'include.config.php';

require_once 'blowfish/blowfish.php';

//als er niet van een confide-ip gerequest wordt niet accepteren
if(!opConfide()){
	echo 'FAALHAASCH: ga fietsen stelen!';
	exit;
}
//we slaan hier alvast de huidige datum en tijd op, dan is het zometeen voor alle ingevoerde
//saldi gelijk.
$datum=getDateTime();

//instellingen laden...
$instellingen=parse_ini_file(ETC_PATH.'/soccie.ini');
if(isset($_POST['saldi'])){
	//blowfish klasse laden
	$blowfish=new Crypt_blowfish($instellingen['secret-key']);
	$sXml=$blowfish->decrypt(base64_decode($_POST['saldi']));
	$sXml=base64_decode($_POST['saldi']);
	//dingen eventueel in een bestand rossen
	$fp=fopen('../../data/soccie.xml', 'w'); fwrite($fp, $sXml);
	//print_r($_POST['saldi']);
	$aSocciesaldi=simplexml_load_string($sXml);
	//controleren of we wel een object krijgen:
	if(is_object($aSocciesaldi)){
		//ff tellen om een getal te melden in de statusmelding
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
				INSERT INTO saldolog
				(
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
				echo 'Een fout. MySQL gaf terug: '.mysql_error()."\r\n";
			}else{
				if(!$db->query($logQuery)){
					echo 'Koppeling voor '.$aSocciesaldo->voornaam.' '.$aSocciesaldo->achternaam.' mislukt'."\r\n";
				}
			}

		}
		if($bOk){
			echo '[ '.$iAantal.' regels ontvangen.... OK ]';
		}else{
			echo 'FAALHAASCH ';
		}
	}else{
		echo 'FAALHAASH, dit is chaos!';
	}
}else{
	echo 'FAALHAASCH, geen input';
}

?>
