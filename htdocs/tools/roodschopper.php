<?php
/*
 * roodschopper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

require_once 'include.config.php';

if($loginlid->getUid()!='0436'){ 
	header('location: http://csrdelft.nl'); 
}

//instellingen
$saldoGrens=-5;
$cie='maalcie';

$naam=array(
	'soccie' => array('SocCie', 'soccie@csrdelft.nl'), 
	'maalcie' => array('MaalCie', 'maalcief@csrdelft.nl'));

$query="
	SELECT uid, ".$cie."Saldo AS saldo
	FROM lid WHERE ".$cie."Saldo<".$saldoGrens." AND 
(status='S_LID');";



$result=$db->query($query);

echo 'Aantal rode mensen met een lager saldo dan '.$saldoGrens.": ".$db->numRows($result)."<hr/>";

while($data=$db->next($result)){
	if($data['uid']=='0641') continue;
	$mail=new Smarty_csr();
	
	$mail->assign('uid', $data['uid']);
	$mail->assign('saldo', number_format($data['saldo'], 2, ',', ''));	
	
	$body=$mail->fetch($cie.'mail.tpl');
	$to=$data['uid'].'@csrdelft.nl, '.$naam[$cie][1].'';
	$subject='U staat rood bij de '.$naam[$cie][0].'.';
	
//	mail($to, $subject, $body, "From: ".$cie."@csrdelft.nl\n\r");
	
	echo nl2br($body).'<hr />';
}
	
	
?>
