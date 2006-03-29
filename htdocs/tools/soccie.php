<?php
error_reporting(E_ALL);


define('LIB_PATH', '/srv/www/www.csrdelft.nl/lib');
define('TMP_PATH', '/srv/www/www.csrdelft.nl/tmp');
define('ETC_PATH', '/srv/www/www.csrdelft.nl/etc/');
ini_set('include_path', LIB_PATH . ':' . ini_get('include_path'));
require_once('class.mysql.php');
require_once('blowfish/blowfish.php');

$db = new MySQL();
$db->connect();

$instellingen=parse_ini_file(ETC_PATH.'/soccie.ini');



if(isset($_POST['saldi'])){
	//blowfish klasse laden
	$blowfish=new Crypt_blowfish($instellingen['secret-key']);
	$sXml=$blowfish->decrypt(base64_decode($_POST['saldi']));
	//dingen in een bestand rossen
	//$fp=fopen('../../data/soccie.xml', 'w'); fwrite($fp, $sXml);
	$aSocciesaldi=simplexml_load_string($sXml);
	//ff tellen
	$iAantal=count($aSocciesaldi);
	$bOk=true;
	foreach($aSocciesaldi as $aSocciesaldo){
		$query="UPDATE socciesaldi SET saldo=".$aSocciesaldo->saldo." WHERE soccieID=".$aSocciesaldo->id." AND createTerm='".$aSocciesaldo->createTerm."' LIMIT 1;";
		//echo $query."\r\n";
		if(!$db->query($query)){
			$bOk=false;
			echo 'Een fout. MySQL gaf terug: '.mysql_error()."\r\n";
		}
	}
	if($bOk){
		echo '[ '.$iAantal.' regels ontvangen.... OK ]';
	}else{
		echo 'FAALHAASCH '.mysql_error();
	}
}else{
	echo 'FAALHAASCH, geen input';
}

?>
