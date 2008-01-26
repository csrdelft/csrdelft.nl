<?php
/*
 * roodschopper.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */
echo 'kwaak'; exit;
 # instellingen & rommeltjes
require_once('include.config.php');

require_once('class.commissie.php');
$soccie=$maalcie=new Commissie($db, $lid);
$soccie->loadCommissie('SocCie');

if($lid->getUid()!='0436'){ 
	header('location: http://csrdelft.nl'); 
}


$query="SELECT uid, soccieSaldo FROM lid WHERE soccieSaldo<-6;";

$result=$db->query($query);


while($data=$db->next($result)){
	$mail=new Smarty_csr();
	
	$mail->assign('uid', $data['uid']);
	$mail->assign('saldo', number_format($data['soccieSaldo'], 2, ',', ''));	
	
	
	$body=$mail->fetch('socciemail.tpl');
	$to=$data['uid'].'@csrdelft.nl, soccie@csrdelft.nl';
	
	mail($to, 'U staat rood bij de SocCie.', $body, "From: soccie@csrdelft.nl.nl\n\r");
	
	echo nl2br($body).'<hr />';
}
	
	
?>
