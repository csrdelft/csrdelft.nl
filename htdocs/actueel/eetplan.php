<?php


require_once 'configuratie.include.php';


if($loginlid->hasPermission('P_LEDEN_READ')) {
	require_once 'eetplan.class.php';
	$eetplan = new Eetplan();
	require_once 'eetplancontent.class.php';
	$midden = new EetplanContent($eetplan);
} else {
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new CmsPagina('geentoegang');
	$midden = new CmsPaginaView($pagina);
}

# pagina weergeven
$pagina=new CsrLayoutPage($midden);
$pagina->view();

?>
