<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# archief.php
# -------------------------------------------------------------------
# Geeft een lijstje met de geärchiveerde couranten weer
# -------------------------------------------------------------------

require_once('include.config.php');

## zijkolom in elkaar jetzen
$zijkolom=new kolom();

# Het middenstuk
if ($lid->hasPermission('P_LEDEN_READ')) {
	require_once('class.courant.php');
	$courant=new Courant();

	require_once('class.courantarchiefcontent.php');
	$body = new CourantArchiefContent($courant);
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}	

# pagina weergeven
$pagina=new csrdelft($body);

$pagina->setZijkolom($zijkolom);

$pagina->view();
	
?>
