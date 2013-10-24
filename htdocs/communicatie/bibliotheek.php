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

//uitgelogd heeft nieuwe layout
if(LoginLid::instance()->hasPermission('P_LOGGED_IN')){
	$layout = '';
} else {
	$layout = 'csrdelft2';
}

$pagina=new csrdelft($biebControl->getContent(), $layout);
//zijkolom kan uitgezet worden
if(!$biebControl->hasZijkolom()){
	$pagina->setZijkolom(false); 
}
//$pagina->addStylesheet('default.css');

$pagina->addStylesheet('bibliotheek.css');
$pagina->addStylesheet('js/datatables/css/datatables_basic.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');

$pagina->addScript('datatables/jquery.dataTables.min.js');
$pagina->addScript('autocomplete/jquery.autocomplete.min.js');

$pagina->addScript('csrdelft.js');
$pagina->addScript('bibliotheek.js');

$pagina->view();