<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# archief.php
# -------------------------------------------------------------------
# Geeft een lijstje met de geärchiveerde couranten weer
# -------------------------------------------------------------------

require_once 'include.config.php';

if ($loginlid->hasPermission('P_LEDEN_READ')) {
	require_once 'courant/class.courant.php';
	$courant=new Courant();

	require_once 'courant/class.courantarchiefcontent.php';
	$body = new CourantArchiefContent($courant);
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$body = new PaginaContent($pagina);
}


$pagina=new csrdelft($body);
$pagina->view();

?>
