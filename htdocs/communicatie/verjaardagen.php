<?php

# instellingen & rommeltjes
require_once('include.config.php');


require_once('class.kolom.php');

if ($lid->hasPermission('P_LEDEN_READ')) {
	# Het middenstuk
	require_once('class.verjaardagcontent.php');
	$midden = new VerjaardagContent('alleverjaardagen');
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}	
## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom); 
$pagina->view();


?>
