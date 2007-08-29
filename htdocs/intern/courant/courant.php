<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant.php
# -------------------------------------------------------------------
# geeft een weergave van een de huidige c.s.r.-courant, of een uit het
# archief
# -------------------------------------------------------------------


require_once('include.config.php');

require_once('class.courant.php');
$courant=new Courant();

# als er genoeg rechten zijn een preview van de csrmail laten zien.
if (!$courant->magBeheren()) { header('location: '.CSR_ROOT); exit; }

//kijken of de huidige getoond moet worden, of een nieuwe
if(isset($_GET['ID']) AND $_GET['ID']!=0){
	$courant->load((int)$_GET['ID']);
}

require_once('class.courantcontent.php');
$pagina=new CourantContent($courant);

$pagina->view();

?>
