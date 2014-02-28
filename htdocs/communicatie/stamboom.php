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
	require_once 'MVC/model/CmsPaginaModel.class.php';
	$model = new CmsPaginaModel();
	$midden = new CmsPaginaView($model->getPagina('geentoegang'));
}

$pagina=new CsrLayoutPage($midden);
$pagina->addStylesheet('stamboom.css');
$pagina->view();

?>
