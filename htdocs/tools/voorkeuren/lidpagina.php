<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bijbelrooster.php
# -------------------------------------------------------------------
# Bijbelrooster.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'voorkeur/overzicht.class.php';

if (LoginLid::mag('P_LEDEN_MOD')) {
	$inhoud = new LidOverzicht();
	if (isset($_GET['lid'])) {
		$inhoud = new LidOverzicht($_GET['lid']);
	}
	if (isset($_POST['opmerkingen'])) {
		$inhoud->save($_POST['opmerkingen']);
	}
	$pagina = new CsrLayoutPage($inhoud);
	$pagina->view();
}
?>