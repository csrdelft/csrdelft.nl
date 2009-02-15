<?php
/*
 * mobiel.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'include.config.php';

$_SESSION['pauper']=true;


$body=new kolom();
require_once 'class.pagina.php';
require_once 'class.paginacontent.php';


$paginacontent = new PaginaContent(new Pagina('mobiel'));
$paginacontent->setActie('bekijken');
$body->addObject($paginacontent);

## pagina weergeven
$pagina=new csrdelft($body);


$pagina->view();
?>
