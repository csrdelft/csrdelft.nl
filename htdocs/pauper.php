<?php
/*
 * mobiel.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'configuratie.include.php';

$_SESSION['pauper']=true;

require_once 'pagina.class.php';
require_once 'MVC/model/CmsPaginaModel.class.php';

$view = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
$view->view();

# Laatste forumberichten
require_once 'forum/forum.class.php';
require_once 'forum/forumcontent.class.php';
$forum=new forum();
$forumcontent=new forumcontent($forum, 'lastposts');

## pagina weergeven
$pagina=new CsrLayoutPage($forumcontent);
$pagina->zijkolom=false;
$pagina->view();
