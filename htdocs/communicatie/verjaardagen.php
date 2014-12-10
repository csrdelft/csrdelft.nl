<?php

require_once 'configuratie.include.php';
require_once 'lid/verjaardag.class.php';

if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	require_once 'lid/verjaardagcontent.class.php';
	$body = new VerjaardagContent('alleverjaardagen');
}

$pagina = new CsrLayoutPage($body);
$pagina->view();
