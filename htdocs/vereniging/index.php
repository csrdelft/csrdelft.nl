<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/vereniging/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('include.config.php');

$body = new Includer('informatie', 'algemeen.html');

$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($body, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();

?>
