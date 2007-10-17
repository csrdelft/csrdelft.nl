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

	$laatste=new CourantArchiefContent($courant);
	$laatste->toggleZijkolom();
	$zijkolom->add($laatste);
} else {
	# geen rechten
	$body = new Includer('', 'geentoegang.html');
}	

# pagina weergeven
$pagina=new csrdelft($body);

$pagina->setZijkolom($zijkolom);

$pagina->view();
	
?>
