<?php

require_once 'configuratie.include.php';
require_once 'verticalecontent.class.php';

if (LoginModel::mag('P_LEDEN_READ')) {
	$midden = new VerticalenContent();

	if (isset($_GET['email'])) {
		$midden->viewEmails($_GET['email']);
		exit;
	}
} else {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

$pagina = new CsrLayoutPage($midden);
$pagina->addStylesheet('/layout/css/verticalen');
$pagina->addScript('/layout/js/verticalen');
$pagina->view();
