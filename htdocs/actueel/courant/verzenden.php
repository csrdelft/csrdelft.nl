<?php
# instellingen & rommeltjes
require_once('include.config.php');



require_once('class.courant.php');
$courant = new Courant();
if(!$courant->magVerzenden()){ header('location: '.CSR_ROOT); exit; }

require_once('class.courantcontent.php');
$mail=new CourantContent($courant);


if(isset($_GET['iedereen'])){
	$mail->zend('leden@csrdelft.nl');	
	$courant->leegCache();
} else {
	$mail->zend('pubcie@csrdelft.nl');
}

?><a href="verzenden.php?iedereen=true"> aan iedereen verzenden</a>
