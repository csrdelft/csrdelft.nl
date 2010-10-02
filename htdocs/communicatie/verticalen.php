<?php


require_once 'configuratie.include.php';
require_once 'verticalecontent.class.php';




if($loginlid->hasPermission('P_LEDEN_READ')) {
	$midden = new VerticalenContent();

	if(isset($_GET['email'])){
		$midden->viewEmails($_GET['email']);
		exit;
	}
}else{
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('verticalen.css');
$pagina->addScript('verticalen.js');
$pagina->view();

?>
