<?php

require_once('include.config.php');

// Het middenstuk
if ($lid->hasPermission('P_DOCS_MOD')) {
	require_once('class.toevoegen.php');
	$upload = new Toevoegen($db, $lid);
	
	require_once('class.toevoegencontent.php');
	$midden = new ToevoegenContent($upload);
} else {
	// geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}	

// zijkolom in elkaar jetzen
$zijkolom=new kolom();

// pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('documenten.css');
$pagina->view();

?>
