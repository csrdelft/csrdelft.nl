<?php


require_once 'include.config.php';
require_once 'class.verticalencontent.php';




if($loginlid->hasPermission('P_LEDEN_READ')) {
	# Het middenstuk
	require_once 'class.motencontent.php' ;
	$midden = new VerticalenContent();
}else{
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('groepen.css');
$pagina->view();

?>
