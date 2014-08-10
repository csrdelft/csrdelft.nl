<?php
/**
 * soccie.php.
 *
 * Dit bestand is de ingang voor anthrax om de saldi te uploaden, elke morgen
 * om 6:30 (cronjob op anthrax). Hier worden de saldi uit de XML getrokken die
 * van Anthrax komt en in de betreffende tabellen in de database gestopt.
 *
 */

require_once 'configuratie.include.php';
require_once 'lid/saldi.class.php';


//als er niet van een confide-ip gerequest wordt niet accepteren
if(!opConfide()){
	echo 'FAALHAASCH: ga fietsen stelen!';
	exit;
}

//instellingen laden...

if(isset($_POST['saldi'])){

	$sXml=base64_decode($_POST['saldi']);
	//dingen eventueel in een bestand rossen
	$fp=fopen('../../data/soccie.xml', 'w'); fwrite($fp, $sXml);

	$status=Saldi::putSoccieXML($sXml);

	echo $status;

}else{
	echo '$_POST[saldi] is niet gezet.';
}
