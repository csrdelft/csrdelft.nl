<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/intern/index.php
# -------------------------------------------------------------------

require_once('include.config.php');

$body = new Includer('', 'leden-thuis.html');

$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($body, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
