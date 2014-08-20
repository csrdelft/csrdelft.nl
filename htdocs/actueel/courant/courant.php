<?php

require_once 'configuratie.include.php';
require_once 'courant/courant.class.php';

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant.php
# -------------------------------------------------------------------
# geeft een weergave van een de huidige c.s.r.-courant, of een uit het
# archief
# -------------------------------------------------------------------

$courant = new Courant();

# als er genoeg rechten zijn een preview van de courant laten zien.
if (!$courant->magToevoegen()) {
	invokeRefresh(CSR_ROOT);
}

//kijken of de huidige getoond moet worden, of een nieuwe
if (isset($_GET['ID']) AND $_GET['ID'] != 0) {
	$courant->load((int) $_GET['ID']);
}

require_once 'courant/courantcontent.class.php';
$pagina = new CourantContent($courant);

$pagina->view();
