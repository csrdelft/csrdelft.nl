<?php

require_once 'configuratie.include.php';


if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$midden = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	require_once 'eetplan.class.php';
	$eetplan = new Eetplan();
	require_once 'eetplancontent.class.php';
	$midden = new EetplanContent($eetplan);
}

# pagina weergeven
$pagina = new CsrLayoutPage($midden);
$pagina->view();
