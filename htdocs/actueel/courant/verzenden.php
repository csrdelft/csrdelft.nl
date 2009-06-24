<?php

require_once 'include.config.php';

require_once 'courant/class.courant.php';
require_once 'courant/class.courantcontent.php';
$courant=new Courant();

//niet verzenden bij geen rechten, en niet bij een lege courant.
if(!$courant->magVerzenden()){
	CourantContent::invokeRefresh('U heeft geen rechten om de courant te verzenden.', CSR_ROOT.'actueel/courant/');
	exit;
}elseif($courant->getBerichtenCount()==0){
	CourantContent::invokeRefresh('Lege courant kan niet worden verzonden', CSR_ROOT.'actueel/courant/');
	exit;
}

$mail=new CourantContent($courant);

if(isset($_GET['iedereen'])){
	$mail->zend('csrmail@lists.knorrie.org');	
	$courant->leegCache();
}else{
	$mail->zend('pubcie@csrdelft.nl');
}

?><a href="verzenden.php?iedereen=true"> aan iedereen verzenden</a>
