<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('include.config.php');

# de pagina-inhoud;
$body = new Includer('', 'links.html');
$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($body);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
