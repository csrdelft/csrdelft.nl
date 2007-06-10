<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('include.config.php');

$body = new Includer('', 'sponsors.html');

$zijkolom=new kolom();

# pagina weergeven

$pagina=new csrdelft($body, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();

?>
