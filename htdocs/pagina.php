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

if (isset($_GET['bewerken']) && $pagina->magBewerken()){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$pagina->setTitel($_POST['titel']);
		$pagina->setInhoud($_POST['inhoud']);
		$pagina->setMenu($_POST['menu']);
		if($pagina->magPermissiesBewerken()){
			$pagina->setRechtenBekijken($_POST['rechten_bekijken']);
			$pagina->setRechtenBewerken($_POST['rechten_bewerken']);
		}
		$pagina->save();
		header('Location: '.CSR_ROOT.'pagina/'.$pagina->getNaam());
	}
	$paginacontent->setActie('bewerken');
	
	$zijkolomlijst = new PaginaContent($pagina);
	$zijkolomlijst->setActie('zijkolom');
	$zijkolom=new Kolom();
	$zijkolom->add($zijkolomlijst);
}elseif($pagina->magBekijken()){
	$paginacontent->setActie('bekijken');
}else{
	$pagina = new Pagina('geentoegang');
	$paginacontent = new PaginaContent($pagina);
}


# pagina weergeven
if($_GET['naam']=='owee' OR $_GET['naam']=='oweeprogramma' OR $_GET['naam']=='video' OR $_GET['naam']=='interesse'){
	$prefix='owee_';
}else{
	$prefix='';
}

// Hier alle namen van pagina's die in de nieuwe layout moeten worden weergegeven
$nieuwNamen = array("contact", "vereniging", "lidworden", "geloof", "vorming", "filmpjes", "gezelligheid", "sport", "vragen", "officieel", "societeit", "ontspanning", "interesse", "interesseverzonden", "accountaanvragen");
if(in_array($_GET['naam'],$nieuwNamen)) {
  	$prefix = 'csrdelft2';
}

$depagina=new csrdelft($paginacontent,$prefix);

if($_GET['naam']=='video'){
	$depagina->setZijkolom(false);
}else{
	if(isset($zijkolom)){
		$depagina->setZijkolom($zijkolom);
	}
}

$depagina->view();
