<?php

require_once 'configuratie.include.php';
require_once 'model/CourantModel.class.php';

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant.php
# -------------------------------------------------------------------
# geeft een weergave van een de huidige c.s.r.-courant, of een uit het
# archief
# -------------------------------------------------------------------

$courant = new CourantModel();

# als er genoeg rechten zijn een preview van de courant laten zien.
if (!$courant->magToevoegen()) {
	redirect(CSR_ROOT);
}

//kijken of de huidige getoond moet worden, of een nieuwe
if (isset($_GET['ID']) AND $_GET['ID'] != 0) {
	$courant->load((int) $_GET['ID']);
}

require_once 'view/courant/CourantView.class.php';
$pagina = new CourantView($courant);

$pagina->view();
