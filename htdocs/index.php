<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');

## zijkolom in elkaar jetzen
$zijkolom=new kolom();

## de pagina-inhoud
$body=new kolom();

# Tekst
require_once 'class.pagina.php';
require_once 'class.paginacontent.php';
$thuis=new Pagina('thuis');
$paginacontent = new PaginaContent($thuis);
$paginacontent->setActie('bekijken');
$body->addObject($paginacontent);

## pagina weergeven
$pagina=new csrdelft($body);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
