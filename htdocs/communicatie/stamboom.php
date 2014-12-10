<?php

require_once 'configuratie.include.php';
require_once 'lid/stamboomcontent.class.php';


if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	if (isset($_GET['uid'])) {
		$uid = $_GET['uid'];
	} else {
		$uid = LoginModel::getUid();
	}
	$body = new StamboomContent($uid);
}

$pagina = new CsrLayoutPage($body);
$pagina->addCompressedResources('stamboom');
$pagina->view();
