<?php
/*
 * mobiel.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'configuratie.include.php';

$_SESSION['pauper']=true;

require_once 'pagina.class.php';
require_once 'paginacontent.class.php';


$paginacontent = new CmsPaginaView(new CmsPagina('mobiel'));
$paginacontent->setActie('bekijken');

# Laatste forumberichten
require_once 'forum/forum.class.php';
require_once 'forum/forumcontent.class.php';
$forum=new forum();
$forumcontent=new forumcontent($forum, 'lastposts');

## pagina weergeven
$pagina=new csrdelft($forumcontent);
$pagina->zijkolom=false;
$pagina->view();
