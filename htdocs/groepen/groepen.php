<?php
/*
 * groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

require_once('include.config.php');

require_once('class.groepen.php');
require_once('class.groep.php');
require_once('class.groepcontent.php');

$groepen=new Groepen('Commissies');
 
$content=new Groepcontent($groepen->getGroep(3));


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($content);
$pagina->setZijkolom($zijkolom);
$pagina->view();
?>
