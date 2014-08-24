<?php

require_once 'configuratie.include.php';
require_once 'lid/stamboomcontent.class.php';


if(LoginModel::mag('P_LEDEN_READ')) {
	if(isset($_GET['uid'])){
		$uid=$_GET['uid'];
	}else{
		$uid=LoginModel::getUid();
	}
	$midden = new StamboomContent($uid);

	
}else{
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

$pagina=new CsrLayoutPage($midden);
$pagina->addStylesheet('/layout/css/stamboom');
$pagina->view();

?>
