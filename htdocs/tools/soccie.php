<?php
error_reporting(E_ALL);


require_once('include.config.php');
require_once('include.common.php');
require_once('class.mysql.php');
require_once('blowfish/blowfish.php');

//als er niet van een confide-ip gerequest wordt niet accepteren
if(!opConfide()){
	echo 'FAALHAASCH: ga fietsen stelen!'; 
	exit;
}
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
			if(!$db->query($query)){
				//scheids, er gaet een kwerie mis, ff een feutmelding printen.
				$bOk=false;
				echo 'Een fout. MySQL gaf terug: '.mysql_error()."\r\n";
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
