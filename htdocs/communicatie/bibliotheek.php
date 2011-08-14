<?php
/*
 * index.php	| 	Gerrit Uitslag (jieter@jpwaag.com)
 *
 * Bibliotheek
 */
require_once 'configuratie.include.php';
require_once 'bibliotheek/bibliotheekcontroller.class.php';

if(isset($_GET['querystring'])){
	$biebControl=new BibliotheekController($_GET['querystring']);
}else{
	die('epic fail');
}

$pagina=new csrdelft($biebControl->getContent());
//zijkolom kan uitgezet worden
if(!$biebControl->hasZijkolom()){
	$pagina->setZijkolom(false); 
}
$pagina->addStylesheet('bibliotheek.css');
$pagina->addStylesheet('js/datatables/css/datatables_basic.css');

$pagina->addScript('datatables/jquery.dataTables.min.js');

$pagina->addScript('csrdelft.js');
$pagina->addScript('bibliotheek.js');
$pagina->addScript('suggest.js');

$pagina->view();
?>
