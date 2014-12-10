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
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
} else {
	require_once 'courant/courant.class.php';
	$courant = new Courant();

	require_once 'courant/courantarchiefcontent.class.php';
	$body = new CourantArchiefContent($courant);
}


$pagina = new CsrLayoutPage($body);
$pagina->view();
