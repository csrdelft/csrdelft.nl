<?php

require_once 'configuratie.include.php';
require_once 'lid/verjaardag.class.php';

if (LoginModel::mag('P_LEDEN_READ')) {
	# Het middenstuk
	require_once 'lid/verjaardagcontent.class.php';
	$midden = new VerjaardagContent('alleverjaardagen');
} else {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

$pagina = new CsrLayoutPage($midden);
$pagina->view();
