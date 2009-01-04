<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# pagina.php
# -------------------------------------------------------------------
# Weergeven van pagina's met tekst uit de database
# -------------------------------------------------------------------

require_once 'include.config.php';

require_once 'class.pagina.php';
require_once 'class.paginacontent.php';

$zijkolom=new kolom();

# de pagina-inhoud
$pagina = new Pagina($_GET['naam']);
$paginacontent = new PaginaContent($pagina);

if (isset($_GET['bewerken']) && $pagina->magBewerken()){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$pagina->setTitel($_POST['titel']);
		$pagina->setInhoud($_POST['inhoud']);
		$pagina->setRechtenBekijken($_POST['rechten_bekijken']);
		$pagina->setRechtenBewerken($_POST['rechten_bewerken']);
		$pagina->save();
		header('Location: '.CSR_ROOT.'pagina/'.$pagina->getNaam());
	}
	$paginacontent->setActie('bewerken');
	
	$zijkolomlijst = new PaginaContent($pagina);
	$zijkolomlijst->setActie('zijkolom');
	$zijkolom->add($zijkolomlijst);
}elseif($pagina->magBekijken()){
	$paginacontent->setActie('bekijken');
}else{
	$pagina = new Pagina('geentoegang');
	$paginacontent = new PaginaContent($pagina);
}

# pagina weergeven
if($_GET['naam']=='owee'){
	$prefix='owee_';
}else{
	$prefix='';
}
$depagina=new csrdelft($paginacontent,$prefix);
if($_GET['naam']=='owee'){
	$depagina->addStylesheet('owee.css');
}
$depagina->setZijkolom($zijkolom);
$depagina->view();

?>
