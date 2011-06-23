<?php

require_once 'configuratie.include.php';

require_once 'fotoalbum.class.php';
require_once 'fotoalbumcontent.class.php';


$base='';

//try to narrow search
preg_match('/fotoalbum\/(.*)$/',$_SERVER['HTTP_REFERER'], $matches);
$file=PICS_PATH.'/fotoalbum/'.urldecode($matches[1]);
if(file_exists($file)){
	$base=$matches[1];
}

echo '<h1>Verwerken wijzigingen in <code>fotoalbum/'.$base.'</code></h1>';

$album=new Fotoalbum($base, urldecode($base));
$album->verwerkFotos();

?>
