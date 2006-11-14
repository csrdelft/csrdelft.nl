<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# Het middenstuk
$midden = new Includer('informatie', 'ondervereniging.html');


## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();
?>
