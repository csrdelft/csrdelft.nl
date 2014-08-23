<?php

require_once 'configuratie.include.php';


if (LoginModel::mag('P_LEDEN_READ')) {
	require_once 'eetplan.class.php';
	$eetplan = new Eetplan();
	require_once 'eetplancontent.class.php';
	$midden = new EetplanContent($eetplan);
} else {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
}

# pagina weergeven
$pagina = new CsrLayoutPage($midden);
$pagina->view();
