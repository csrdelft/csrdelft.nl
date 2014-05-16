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
	$inhoud = new CommissieOverzicht();
	if (isset($_GET['c'])) {
		$inhoud = new CommissieOverzicht($_GET['c']);
	}
	$pagina = new CsrLayoutPage($inhoud);
	$pagina->view();
}
?>