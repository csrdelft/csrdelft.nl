<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# de pagina-inhoud;
$body = new Includer('informatie', 'contact.html');
$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($body, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
