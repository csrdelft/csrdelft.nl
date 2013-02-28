<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once 'configuratie.include.php';

if($loginlid->hasPermission('P_LOGGED_IN')) {
    header("Location: ". CSR_SERVER . "/leden");
}

## de pagina-inhoud
$body=new kolom();

# Tekst
require_once 'pagina.class.php';
require_once 'paginacontent.class.php';
$thuis=new Pagina('thuis');
$paginacontent = new PaginaContent($thuis);
$paginacontent->setActie('bekijken');
$body->addObject($paginacontent);

## pagina weergeven
$pagina=new csrdelft($body);
$pagina->view("index");
?>
