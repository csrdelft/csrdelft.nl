<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.eetplan.php');
	$eetplan = new Eetplan();
	require_once('class.eetplancontent.php');
	$midden = new EetplanContent($eetplan);
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
