<?php

require_once 'configuratie.include.php';
require_once 'lid/verjaardag.class.php';


if($loginlid->hasPermission('P_LEDEN_READ')){
	# Het middenstuk
	require_once('lid/verjaardagcontent.class.php');
	$midden = new VerjaardagContent('alleverjaardagen');
} else {
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->view();

?>
