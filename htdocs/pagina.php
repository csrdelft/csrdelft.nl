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
} else {
	$paginacontent->setActie('bekijken');
}

$zijkolom=new kolom();

# pagina weergeven
$depagina=new csrdelft($paginacontent);
$depagina->setZijkolom($zijkolom);
$depagina->view();

?>
