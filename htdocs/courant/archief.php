<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# archief.php
# -------------------------------------------------------------------
# Geeft een lijstje met de geärchiveerde couranten weer
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_LEDEN_READ') OR ! LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'model/CmsPaginaModel.class.php';
	require_once 'view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	require_once 'model/CourantModel.class.php';
	$courant = new CourantModel();

	require_once 'view/courant/CourantArchiefView.class.php';
	$body = new CourantArchiefView($courant);
}


$pagina = new CsrLayoutPage($body);
$pagina->view();
