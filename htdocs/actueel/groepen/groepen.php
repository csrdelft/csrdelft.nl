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

if(isset($_GET['gtype'])){
	$gtype=$_GET['gtype'];
}else{
	$gtype="Commissies";
}
$groepen=new Groepen($gtype);
 
$content=new Groepencontent($groepen);


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($content);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('groepen.css');
$pagina->view();
?>
