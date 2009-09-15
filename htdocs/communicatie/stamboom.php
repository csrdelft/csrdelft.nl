<?php

require_once 'include.config.php';
require_once 'lid/class.stamboomcontent.php';


if($loginlid->hasPermission('P_LEDEN_READ')) {
	if(isset($_GET['uid'])){
		$uid=$_GET['uid'];
	}else{
		$uid=$loginlid->getUid();
	}
	$midden = new StamboomContent($uid);

	
}else{
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('stamboom.css');
$pagina->view();

?>
