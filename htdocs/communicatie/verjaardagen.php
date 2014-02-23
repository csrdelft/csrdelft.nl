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
	$pagina=new CmsPagina('geentoegang');
	$midden = new CmsPaginaView($pagina);
}

$pagina=new CsrLayoutPage($midden);
$pagina->view();

?>
