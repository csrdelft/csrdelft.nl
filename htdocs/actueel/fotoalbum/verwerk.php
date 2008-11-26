<?php
require_once 'include.config.php';

require_once 'class.fotoalbum.php';
require_once 'class.fotoalbumcontent.php';

$album=new Fotoalbum('','');
$album->verwerkFotos();

?>