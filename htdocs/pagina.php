<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# pagina.php
# -------------------------------------------------------------------
# Weergeven van pagina's met tekst uit de database
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'pagina.class.php';
require_once 'paginacontent.class.php';


# de pagina-inhoud
$pagina = new Pagina($_GET['naam']);
$paginacontent = new PaginaContent($pagina);

if (isset($_GET['bewerken']) && $pagina->magBewerken()) {
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$pagina->setTitel($_POST['titel']);
		$pagina->setInhoud($_POST['inhoud']);
		$pagina->setMenu($_POST['menu']);
		if ($pagina->magPermissiesBewerken()) {
			$pagina->setRechtenBekijken($_POST['rechten_bekijken']);
			$pagina->setRechtenBewerken($_POST['rechten_bewerken']);
		}
		$pagina->save();
		header('Location: ' . CSR_ROOT . 'pagina/' . $pagina->getNaam());
	}
	$paginacontent->setActie('bewerken');

	$zijkolomlijst = new PaginaContent($pagina);
	$zijkolomlijst->setActie('zijkolom');
}
elseif ($pagina->magBekijken()) {
	$paginacontent->setActie('bekijken');
}
else {
	$pagina = new Pagina('geentoegang');
	$paginacontent = new PaginaContent($pagina);
}

// Hier alle namen van pagina's die in de nieuwe layout moeten worden weergegeven
$nieuwNamen = array("contact", "csrindeowee", "vereniging", "lidworden", "geloof", "vorming", "filmpjes", "gezelligheid", "sport", "vragen", "officieel", "societeit", "ontspanning", "interesse", "interesseverzonden", "accountaanvragen");
if (in_array($_GET['naam'], $nieuwNamen) && !LoginLid::instance()->hasPermission('P_LEDEN_READ')) {
	// uitgelogde bezoeker heeft nieuwe layout
	$depagina = new csrdelft2($paginacontent);
	
	$nieuwNamen = array("vereniging", "geloof", "vorming", "gezelligheid", "sport", "ontspanning", "societeit", "officieel");
	if (in_array($_GET['naam'], $nieuwNamen)) {
		$depagina->setMenu('Vereniging');
	}
}
else {
	$depagina = new csrdelft($paginacontent);

	if (isset($zijkolomlijst)) {
		$depagina->addZijkolom($zijkolomlijst);
	}
}

$depagina->view();
