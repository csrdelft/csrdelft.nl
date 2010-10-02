<?php

require_once 'configuratie.include.php';

require_once 'fotoalbum.class.php';
require_once 'fotoalbumcontent.class.php';

$album=new Fotoalbum('','');
$album->verwerkFotos();

?>
