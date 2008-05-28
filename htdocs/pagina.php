<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Weergeven van pagina's met tekst uit de database
# -------------------------------------------------------------------

require_once 'include.config.php';

require_once 'class.pagina.php';
require_once 'class.paginacontent.php';

# de pagina-inhoud
$pagina = new Pagina($_GET['naam']);
$paginacontent = new PaginaContent($pagina);

if (isset($_GET['bewerken']) && $pagina->magBewerken()){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$pagina->setTitel($_POST['titel']);
		$pagina->setInhoud($_POST['inhoud']);
		$pagina->save();
		header('Location: '.CSR_ROOT.'pagina/'.$pagina->getNaam());
	}
	$paginacontent->setActie('bewerken');
}elseif($pagina->magBekijken()){
	$paginacontent->setActie('bekijken');
}else{
	$pagina = new Pagina('geentoegang');
	$paginacontent = new PaginaContent($pagina);
}

$zijkolom=new kolom();

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
