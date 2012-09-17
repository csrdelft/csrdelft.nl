<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bijbelrooster.php
# -------------------------------------------------------------------
# Bijbelrooster.
# -------------------------------------------------------------------

require_once('configuratie.include.php');

require_once('voorkeur/overzicht.class.php');
if(LoginLid::instance()->hasPermission('P_ADMIN,P_BESTUUR')){
	$inhoud = new LidOverzicht();
	if(isset($_GET['lid']))
		$inhoud = new LidOverzicht($_GET['lid']);
	if(isset($_POST['opmerkingen']))
		$inhoud->save($_POST['opmerkingen']);
	$pagina = new csrdelft($inhoud);
	$pagina->view();
}
?>