<?php
# instellingen & rommeltjes
require_once('include.config.php');


// Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.documenten.php');	
	$documenten = new Documenten($lid, $db);
	
	require_once('class.documentencontent.php');
	$midden=new DocumentenContent($documenten);
}else{ 
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

// zijkolom in elkaar jetzen
$zijkolom=new kolom();

// pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('documenten.css');
$pagina->view();

?>
