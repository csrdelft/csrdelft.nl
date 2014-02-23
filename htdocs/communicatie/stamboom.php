<?php

require_once 'configuratie.include.php';
require_once 'lid/stamboomcontent.class.php';


if($loginlid->hasPermission('P_LEDEN_READ')) {
	if(isset($_GET['uid'])){
		$uid=$_GET['uid'];
	}else{
		$uid=$loginlid->getUid();
	}
	$midden = new StamboomContent($uid);

	
}else{
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new CmsPagina('geentoegang');
	$midden = new CmsPaginaView($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('stamboom.css');
$pagina->view();

?>
