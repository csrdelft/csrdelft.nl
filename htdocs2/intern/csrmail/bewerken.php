<?php
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	
# Het middenstuk
if ($lid->hasPermission('P_MAIL_COMPOSE')) {
	require_once('class.csrmail.php');
	$csrmail = new Csrmail($lid, $db);
	require_once('class.csrmailcomposecontent.php');
	$body = new Csrmailcomposecontent($csrmail);
} else {
	# geen rechten
	$body = new Includer('', 'geentoegang.html');
}	



# pagina weergeven
$pagina=new csrdelft($body,  $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
