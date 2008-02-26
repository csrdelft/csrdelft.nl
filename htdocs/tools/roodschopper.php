<?php
/*
 * roodschopper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

exit;

 # instellingen & rommeltjes
require_once('include.config.php');

if($lid->getUid()!='0436'){ 
	header('location: http://csrdelft.nl'); 
}

$saldoGrens=-7;

$query="SELECT uid, soccieSaldo FROM lid WHERE soccieSaldo<".$saldoGrens.";";



$result=$db->query($query);

echo 'Aantal rode mensen met een lager saldo dan '.$saldoGrens.": ".$db->numRows($result)."<hr/>";

while($data=$db->next($result)){
	$mail=new Smarty_csr();
	
	$mail->assign('uid', $data['uid']);
	$mail->assign('saldo', number_format($data['soccieSaldo'], 2, ',', ''));	
	
	
	$body=$mail->fetch('socciemail.tpl');
	$to=$data['uid'].'@csrdelft.nl, soccie@csrdelft.nl';
	
	mail($to, 'U staat rood bij de SocCie.', $body, "From: soccie@csrdelft.nl\n\r");
	
	echo nl2br($body).'<hr />';
}
	
	
?>
