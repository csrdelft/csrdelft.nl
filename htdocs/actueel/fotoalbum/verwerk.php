<?php

require_once 'configuratie.include.php';

require_once 'fotoalbum.class.php';
require_once 'fotoalbumcontent.class.php';


define('RESIZE_OUTPUT', null);

$base = '';

//try to narrow search
preg_match('/fotoalbum\/(.*)$/', $_SERVER['HTTP_REFERER'], $matches);
$file = PICS_PATH . '/fotoalbum/' . urldecode($matches[1]);

if (file_exists($file)) {
	$base = $matches[1];
}

$base = urldecode($base);

echo '<h1>Verwerken wijzigingen in <code>fotoalbum/' . mb_htmlentities($base) . '</code></h1>';
echo 'Dit kan even duren<br />';

flush();

$album = new Fotoalbum($base, $base);
if (!$album->exists()) {
	$album = new Fotoalbum('', '');
}
$album->verwerkFotos();
?>