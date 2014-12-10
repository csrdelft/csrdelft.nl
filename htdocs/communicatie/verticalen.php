<?php

require_once 'configuratie.include.php';
require_once 'verticalecontent.class.php';

if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	$body = new VerticalenContent();

	if (isset($_GET['email'])) {
		$body->viewEmails($_GET['email']);
		exit;
	}
}

$pagina = new CsrLayoutPage($body);
$pagina->addCompressedResources('verticalen');
$pagina->view();
