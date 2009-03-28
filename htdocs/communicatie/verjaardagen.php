<?php

require_once 'include.config.php';
require_once 'lid/class.mootverjaardag.php';


if($loginlid->hasPermission('P_LEDEN_READ')){
	# Het middenstuk
	require_once('class.verjaardagcontent.php');
	$midden = new VerjaardagContent('alleverjaardagen');
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->view();

?>
