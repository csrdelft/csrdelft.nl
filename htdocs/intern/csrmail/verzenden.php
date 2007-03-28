<?php
# instellingen & rommeltjes
require_once('include.config.php');

# als er genoeg rechten zijn een preview van de csrmail laten zien.
if (!$lid->hasPermission('P_MAIL_COMPOSE')) { header('location: '.CSR_ROOT); }
require_once('class.csrmail.php');
$csrmail = new Csrmail($lid, $db);
require_once('class.csrmailcontent.php');
require_once('class.csrmailcomposecontent.php');
$csrmailbeheer = new Csrmailcomposecontent($csrmail);

if(isset($_GET['iedereen'])){
	$csrmailbeheer->zend('csrmail@lists.jeugdkerken.nl');	
	$csrmail->clearCache();
}
$csrmailbeheer->zend('pubcie@csrdelft.nl');

?><a href="verzenden.php?iedereen=true"> aan iedereen verzenden</a>
