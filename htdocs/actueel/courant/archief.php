<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# archief.php
# -------------------------------------------------------------------
# Geeft een lijstje met de geärchiveerde couranten weer
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

if ($loginlid->hasPermission('P_LEDEN_READ')) {
	require_once 'courant/courant.class.php';
	$courant=new Courant();

	require_once 'courant/courantarchiefcontent.class.php';
	$body = new CourantArchiefContent($courant);
}else{
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new CmsPagina('geentoegang');
	$body = new CmsPaginaView($pagina);
}


$pagina=new CsrLayoutPage($body);
$pagina->view();

?>
