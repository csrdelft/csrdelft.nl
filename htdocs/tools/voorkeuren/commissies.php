<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bijbelrooster.php
# -------------------------------------------------------------------
# Bijbelrooster.
# -------------------------------------------------------------------

require_once('configuratie.include.php');

require_once('voorkeur/overzicht.class.php');
if(LoginLid::instance()->hasPermission('P_ADMIN,P_BESTUUR' OR LoginLid::instance()->getLid()->->isBestuur())){
	$inhoud = new CommissieOverzicht();
	if(isset($_GET['c']))
		$inhoud = new CommissieOverzicht($_GET['c']);
	$pagina = new csrdelft($inhoud);
	$pagina->view();
}
?>