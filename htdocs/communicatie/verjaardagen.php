<?php

require_once 'configuratie.include.php';
require_once 'lid/verjaardag.class.php';


if($loginlid->hasPermission('P_LEDEN_READ')){
	# Het middenstuk
	require_once('lid/verjaardagcontent.class.php');
	$midden = new VerjaardagContent('alleverjaardagen');
} else {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	$model = new CmsPaginaModel();
	$midden = new CmsPaginaView($model->getPagina('geentoegang'));
}

$pagina=new CsrLayoutPage($midden);
$pagina->view();

?>
