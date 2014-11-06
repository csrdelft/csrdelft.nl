<?php

require_once 'configuratie.include.php';
require_once 'voorkeur/overzicht.class.php';

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# commissies.php
# -------------------------------------------------------------------
# Commissies.
# -------------------------------------------------------------------

if (LoginModel::mag('groep:bestuur')) {
	$inhoud = new CommissieOverzicht();
	if (isset($_GET['c'])) {
		$inhoud = new CommissieOverzicht($_GET['c']);
	}
	$pagina = new CsrLayoutPage($inhoud);
	$pagina->view();
}
