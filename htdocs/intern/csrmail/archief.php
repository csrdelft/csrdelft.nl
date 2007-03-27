<?php

# instellingen & rommeltjes
require_once('include.config.php');


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();


# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.csrmail.php');
	$csrmail = new Csrmail($lid, $db);
	require_once('class.csrmailcontent.php');
	require_once('class.csrmailarchiefcontent.php');
	$body = new CsrmailarchiefContent($csrmail);
	if(isset($_GET['iframe'])){ 
		$body->view(); 
		exit;
	}
	$laatste=new CsrmailarchiefContent($csrmail);
	$laatste->setZijkolom();
	$zijkolom->add($laatste);
} else {
	# geen rechten
	$body = new Includer('', 'geentoegang.html');
}	


	

# pagina weergeven
$pagina=new csrdelft($body,  $lid, $db);

$pagina->setZijkolom($zijkolom);


$pagina->view();
	

?>
