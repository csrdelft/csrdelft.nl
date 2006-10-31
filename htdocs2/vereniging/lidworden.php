<?php
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

$midden = new Includer('informatie', 'lidworden.php');

$zijkolom=new kolom();
# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
